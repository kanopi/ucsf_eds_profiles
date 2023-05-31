<?php

namespace Drupal\ucsf_eds_profiles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ldap_servers\LdapBridgeInterface;
use Symfony\Component\Ldap\LdapInterface;

/**
 * Gets the LDAP interface from configuration.
 */
class UcsfEdsProfilesLdapFactory {

  use StringTranslationTrait;

  /**
   * The module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The module logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The LDAP Bridge.
   *
   * @var \Drupal\ldap_servers\LdapBridgeInterface
   */
  protected $ldapBridge;

  /**
   * Initialization method.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config.factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The logger.factory service.
   * @param \Drupal\ldap_servers\LdapBridgeInterface $ldapBridge
   *   The ldap.bridge service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, LoggerChannelFactoryInterface $loggerChannelFactory, LdapBridgeInterface $ldapBridge) {
    $this->config = $configFactory->get('ucsf_eds_profiles.settings');
    $this->logger = $loggerChannelFactory->get('ucsf_eds_profiles');
    $this->ldapBridge = $ldapBridge;
  }

  /**
   * Gets the configured LDAP connection.
   *
   * @todo turn this into a factory.
   *
   * @return \Symfony\Component\Ldap\LdapInterface|null
   *   The LDAP connection.
   */
  public function get(): ?LdapInterface {
    $ldap_enabled = $this->config->get('ldap_enabled');
    if (!$ldap_enabled) {
      return NULL;
    }

    $ldap_mn = $this->config->get('ldap_mn');
    if (!$ldap_mn) {
      $this->logger->error($this->t('Ldap server name missing in /admin/config/ucsf_eds_profiles'));
      return NULL;
    }

    $this->ldapBridge->setServerById($ldap_mn);
    if (!$this->ldapBridge->bind()) {
      $this->logger->error($this->t('Ldap bind failed: %ldap_mn', ['%ldap_mn' => $ldap_mn]));
      return NULL;
    }

    if ($ldap = $this->ldapBridge->get()) {
      return $ldap;
    }

    $this->logger->error('Ldap server undefined');
    return NULL;
  }

}
