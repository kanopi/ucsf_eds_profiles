<?php

namespace Drupal\ucsf_eds_profiles\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\ucsf_eds_profiles\UcsfEdsProfilesLdapManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Synchronizes EDS and Profiles nodes with data source.
 *
 * @QueueWorker(
 *   id = "ucsf_eds_profiles_sync",
 *   title = @Translation("Synchronizes EDS and Profiles nodes with data source."),
 *   cron = {"time" = 60}
 * )
 */
final class UcsfEdsProfilesSyncQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The LDAP Manager service.
   *
   * @var \Drupal\ucsf_eds_profiles\UcsfEdsProfilesLdapManagerInterface
   */
  protected $ldapManager;

  /**
   * Initialization method.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity_type.manager service.
   * @param \Drupal\ucsf_eds_profiles\UcsfEdsProfilesLdapManagerInterface $ldapManager
   *   The ucsf_eds_profiles.ldap_manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, UcsfEdsProfilesLdapManagerInterface $ldapManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->ldapManager = $ldapManager;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($data);

    $this->ldapManager->sync($node);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('ucsf_eds_profiles.ldap_manager')
    );
  }

}
