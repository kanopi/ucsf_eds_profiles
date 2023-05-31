<?php

namespace Drupal\ucsf_eds_profiles\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\node\NodeInterface;
use Drupal\ucsf_eds_profiles\UcsfEdsProfilesLdapManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a page to update the profile from UCSF EDS Profiles API.
 */
final class UcsfEdsProfilesController extends ControllerBase implements ContainerInjectionInterface {

  use MessengerTrait;

  /**
   * The UCSF EDS Profiles LDAP Manager.
   *
   * @var \Drupal\ucsf_eds_profiles\UcsfEdsProfilesLdapManagerInterface
   */
  protected $ldapManager;

  public function __construct(UcsfEdsProfilesLdapManagerInterface $ldapManager) {
    $this->ldapManager = $ldapManager;
  }

  /**
   * Updates a UCSF EDS Profile node from LDAP.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The profile node.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects back to the Node page.
   */
  public function update_eds_node(NodeInterface $node): RedirectResponse {
    $nid = $node->id();

    if ($node->bundle() == 'ucsf_eds_profiles' && $this->ldapManager->hasLdap()) {
      $updated = $this->ldapManager->sync($node);
      $message = $updated ? 'Updated' : 'No change.';
      $this->messenger()->addMessage($message);
    }
    else {
      $this->messenger()->addMessage('Nothing happened - update EDS only works on UCSF EDS nodes with LDAP synchronization enabled.');
    }

    $response = new RedirectResponse('/node/' . $nid);
    return $response->send();
  }

  /**
   * Checks if this route can be updated.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to cehck.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function checkAccess(NodeInterface $node): AccessResultInterface {
    return AccessResult::allowedif($node->bundle() === 'ucsf_eds_profiles');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('ucsf_eds_profiles.ldap_manager'));
  }

}
