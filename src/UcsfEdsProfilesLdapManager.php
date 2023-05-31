<?php

namespace Drupal\ucsf_eds_profiles;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\Ldap\Entry;

/**
 * Provides methods to manager UCSF EDS Profiles from LDAP.
 */
class UcsfEdsProfilesLdapManager implements UcsfEdsProfilesLdapManagerInterface {

  use StringTranslationTrait;

  /**
   * The module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The email.validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The LDAP connection.
   *
   * @var \Symfony\Component\Ldap\LdapInterface|null
   */
  protected $ldap;

  /**
   * The module logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Initialization method.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config.factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger.factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity_type.manager service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database service.
   * @param \Drupal\ucsf_eds_profiles\UcsfEdsProfilesLdapFactory $ldapFactory
   *   The ucsf_eds_profiles.ldap_factory service.
   * @param \Drupal\Component\Utility\EmailValidatorInterface $emailValidator
   *   The email.validator service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module.handler service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, LoggerChannelFactoryInterface $loggerChannelFactory, EntityTypeManagerInterface $entityTypeManager, Connection $connection, UcsfEdsProfilesLdapFactory $ldapFactory, EmailValidatorInterface $emailValidator, ModuleHandlerInterface $moduleHandler) {
    $this->config = $configFactory->get('ucsf_eds_profiles.settings');
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $loggerChannelFactory->get('ucsf_eds_profiles');
    $this->ldap = $ldapFactory->get();
    $this->emailValidator = $emailValidator;
    $this->moduleHandler = $moduleHandler;
    $this->database = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function hasLdap(): bool {
    return $this->ldap !== NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function search(string $basedn, string $filter): Entry|FALSE|null {
    try {
      if (!$this->ldap) {
        return NULL;
      }

      $result = $this->ldap->query($basedn, $filter)->execute();
      return isset($result[0]) && !empty($result[0]) ? $result[0] : NULL;
    }
    catch (\Exception $e) {
      $this->logger->error('Ldap search error', ['exception' => $e]);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function searchByEmail(string $email): Entry|FALSE|null {
    if ($this->emailValidator->isValid($email)) {
      return $this->search('ou=people,dc=ucsf,dc=edu', 'mail=' . $email);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function searchByUcid(string $ucid): Entry|FALSE|null {
    return $this->search('ou=people,dc=ucsf,dcedu', 'ucsfEduIDNumber=' . $ucid);
  }

  /**
   * {@inheritdoc}
   */
  public function searchByDept(string $dept_num): Entry|FALSE|null {
    return $this->search('ou=departments,dc=ucsf,dc=edu', 'ou=' . $dept_num);
  }

  /**
   * {@inheritdoc}
   */
  public function searchAddressByUid(string $uid, string $address_type = 'Campus Address'): Entry|FALSE|null {
    return $this->search('cn=' . $address_type . ',uid=' . $uid . ',ou=people,dc=ucsf,dc=edu', 'cn=' . $address_type);
  }

  /**
   * {@inheritdoc}
   */
  public function sync(NodeInterface $node): bool {
    if ($this->ldap) {
      $email = $node->label();
      if (empty($email)) {
        $this->logger->error('Required email value is missing');
        return FALSE;
      }
      elseif (!$this->emailValidator->isValid($email)) {
        $this->logger->error('Required email is not a valid email address');
      }

      $eds = $this->searchByEmail($email);
      // No EDS record, set archive flag.
      // @todo set archive flag?
      if ($eds === NULL) {
        return FALSE;
      }

      // EDS connection bad, log and do nothing for now.
      if ($eds === FALSE) {
        $this->logger->error('EDS search by email failed - unable to continue node sync.');
        return FALSE;
      }

      $uid = !$eds ? NULL : $eds->getAttribute('uid')[0];
      $eds_addr_campus = ($uid) ? $this->searchAddressByUid($uid) : FALSE;
      $eds_addr_practice = ($uid) ? $this->searchAddressByUid($uid, 'Private Practice Address') : FALSE;

      $dept_num = ($eds) ? $eds->getAttribute('ucsfEduPrimaryDepartmentNumber')[0] : NULL;
      $eds_dept = ($dept_num) ? $this->searchByDept($dept_num) : FALSE;

      // Array to hold data to give hooks a chance to modify before saving.
      $values = [];

      // Put EDS data into $values.
      foreach (UcsfEdsProfilesLdapManagerInterface::EDS_FIELD_MAP as $fn => $source_fn) {
        if ($node->hasField($fn)) {
          switch ($fn) {
            case 'field_ucsfeds_address':
            case 'field_ucsfeds_phone':
            case 'field_ucsfeds_phonereleasecode':
              $values[$fn] = ($eds_addr_campus && $eds_addr_campus->hasAttribute($source_fn)) ? $eds_addr_campus->getAttribute($source_fn)[0] : NULL;
              break;
            case 'field_ucsfeds_addressp':
            case 'field_ucsfeds_phonep':
            case 'field_ucsfeds_phonereleasecodep':
              $values[$fn] = ($eds_addr_practice && $eds_addr_practice->hasAttribute($source_fn)) ? $eds_addr_practice->getAttribute($source_fn)[0] : NULL;
              break;
            case 'field_ucsfeds_primarydeptorunit':
              $values[$fn] = ($eds_dept && $eds_dept->hasAttribute($source_fn)) ? $eds_dept->getAttribute($source_fn)[0] : NULL;
              break;
            default:
              $values[$fn] = ($eds && $eds->hasAttribute($source_fn)) ? $eds->getAttribute($source_fn)[0] : NULL;
          }
        }
      }

      // Put Profiles data into $values, if it exists.
      $ucid = !$eds ? NULL : $eds->getAttribute('ucsfEduIDNumber')[0];
      $profile = ($ucid) ? $this->searchByUcid($ucid) : FALSE;
      if ($profile) {
        foreach (UcsfEdsProfilesLdapManagerInterface::PROFILES_FIELD_MAP as $fn => $source_fn) {
          if ($node->hasField($fn)) {
            switch ($fn) {
              case 'field_ucsfeds_prfpublications':
                // Gets all PublicationID for this person.
                $pids = (!empty($profile[$source_fn])) ? array_column($profile[$source_fn], 'PublicationID') : NULL;
                if ($pids) {
                  // Create/update Publication nodes and gets back
                  // [pid => node id].
                  $p_nids = $this->syncPublications($profile[$source_fn]);
                  foreach ($pids as $index => $pid) {
                    $values[$fn][$index] = (isset($p_nids[$pid])) ? ['target_id' => $p_nids[$pid]] : NULL;
                  }
                }
                else {
                  $values[$fn] = NULL;
                }
                break;
              case 'field_ucsfeds_prfawardshonors':
                $values[$fn] = (!empty($profile[$source_fn])) ? array_column($profile[$source_fn], 'Summary') : NULL;
                break;
              case 'field_ucsfeds_prfcollabinterests':
                $values[$fn] = (!empty($profile[$source_fn])) ? explode(', ', $profile[$source_fn]['Summary']) : NULL;
                break;
              default:
                $values[$fn] = (!empty($profile[$source_fn])) ? $profile[$source_fn] : NULL;
            }
          }
        }
      }

      // Remove values with release codes that are not in the allowed release
      // codes setting.
      $allowed_release_codes = $this->config->get('allowed_release_codes');

      if (isset($values['field_ucsfeds_emailreleasecode']) && isset($values['field_ucsfeds_email'])) {
        if (!in_array($values['field_ucsfeds_emailreleasecode'], $allowed_release_codes)) {
          $values['field_ucsfeds_email'] = NULL;
        }
      }
      if (isset($values['field_ucsfeds_faxreleasecode']) && isset($values['field_ucsfeds_fax'])) {
        if (!in_array($values['field_ucsfeds_faxreleasecode'], $allowed_release_codes)) {
          $values['field_ucsfeds_fax'] = NULL;
        }
      }
      if (isset($values['field_ucsfeds_phonereleasecode']) && isset($values['field_ucsfeds_phone'])) {
        if (!in_array($values['field_ucsfeds_phonereleasecode'], $allowed_release_codes)) {
          $values['field_ucsfeds_phone'] = NULL;
        }
      }
      if (isset($values['field_ucsfeds_phonereleasecodep']) && isset($values['field_ucsfeds_phonep'])) {
        if (!in_array($values['field_ucsfeds_phonereleasecodep'], $allowed_release_codes)) {
          $values['field_ucsfeds_phonep'] = NULL;
        }
      }
      if (isset($values['field_ucsfeds_entryreleasecode'])) {
        if (!in_array($values['field_ucsfeds_entryreleasecode'], $allowed_release_codes)) {
          foreach (UcsfEdsProfilesLdapManagerInterface::EDS_FIELD_MAP as $fn => $v) {
            if (isset($values[$fn])) {
              $values[$fn] = NULL;
            }
          }
          foreach (UcsfEdsProfilesLdapManagerInterface::PROFILES_FIELD_MAP as $fn => $v) {
            if (isset($values[$fn])) {
              $values[$fn] = NULL;
            }
          }
        }
      }

      // Allow hook to add additional fields for synchronization or modify
      // existing values.
      $values = $this->moduleHandler
        ->invokeAll('ucsf_eds_profiles_node_sync_pre_save', [
          $values,
          $node,
          $eds,
          $eds_addr_campus,
          $eds_addr_practice,
          $eds_dept,
          $profile,
        ]);

      // Set node fields to stored and processed values, and set save flag to
      // TRUE if any values will change from the previous revision.
      $save_flag = $node->isNew();
      $unchanged = ($nid = $node->id()) ? $this->entityTypeManager->getStorage('node')->loadUnchanged($nid) : NULL;
      foreach ($values as $fn => $value) {
        if ($node->hasField($fn)) {
          $node->set($fn, $value);
          if (!$save_flag && !($node->{$fn}->equals($unchanged->{$fn}))) {
            $save_flag = TRUE;
          }
        }
      }

      // Only save node if values have changed.
      if ($save_flag) {
        // @todo maybe log changes?
        $node->set('revision_log', 'Node synchronized with source data at UCSF EDS and UCSF Profiles.');
        $node->setNewRevision();
        try {
          $node->save();
          return TRUE;
        }
        catch (\Exception $e) {
          $this->logger->error('Error saving node', ['exception' => $e]);
        }
      }
    }

    return FALSE;
  }

  /**
   * Creates new or updates existing UCSF Profiles Publications nodes.
   *
   * @param array $publications
   *   An array of publication data from UCSF Profiles api call for a single
   *   person.
   *
   * @return int[]
   *   An array of PublicationID to NID.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function syncPublications(array $publications): array {
    // Find existing publication nodes keyed by PublicationID.
    $pids = array_column($publications, 'PublicationID');
    $storage = $this->entityTypeManager->getStorage('node');

    // Gets all existing field data.
    $query = $this->database->select('node__field_ucsfpfpub_publicationid', 'fd');
    $query
      ->fields('fd', ['field_ucsfpufpub_publicationid_uri', 'entity_id'])
      ->condition('field_ucsfpfpub_publicationid_uri', $pids, 'IN');
    $p_nids = $query->execute()->fetchAllKeyed(0, 1);

    // Create or update Publications nodes.
    foreach ($pids as $index => $pid) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = (isset($p_nids[$pid])) ? $storage->load($p_nids[$pid]) : $storage->create(['type' => 'ucsf_profiles_publication']);
      $save_flag = $node->isNew();

      foreach (UcsfEdsProfilesLdapManagerInterface::PUBLICATIONS_FIELD_MAP as $p_fn => $p_source_fn) {
        if ($node->hasField($p_fn)) {
          $node_value = $node->{$p_fn}->getString();
          switch ($p_fn) {
            case 'field_ucsfpfpub_sourcename':
            case 'field_ucsfpfpub_sourceurl':
            case 'field_ucsfpfpub_pmid':
              $source_value = $publications[$index]['PublicationSource'][0][$p_source_fn];
              break;
            default:
              $source_value = $publications[$index][$p_source_fn];
          }
          if ($node_value != $source_value) {
            $node->set($p_fn, $source_value ?? NULL);
            $save_flag = TRUE;
          }
        }
      }

      if ($save_flag) {
        try {
          $node->set('revision_log', 'Node synchronized with source data at UCSF Profiles.');
          $node->setNewRevision();
          $node->save();
        }
        catch (\Exception $e) {
          $this->logger->error('Failed to save publication', ['exception' => $e]);
        }
      }

      // Only sets an nid if a publication was successfully created or already
      // existed.
      if (($nid = $node->id()) !== NULL) {
        $p_nids[$pid] = $nid;
      }
    }

    return $p_nids;
  }

}
