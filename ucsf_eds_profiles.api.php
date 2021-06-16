<?php

use Symfony\Component\Ldap\Entry;
use Drupal\node\NodeInterface;

/**
 * @file
 * Hooks for ucsf_eds_profiles module.
 */

/**
 * Allows altering values before being saved to the node.
 *
 * @param array $values
 * @param \Drupal\node\NodeInterface $node
 * @param \Symfony\Component\Ldap\Entry $eds
 * @param \Symfony\Component\Ldap\Entry|false $eds_addr_campus
 * @param \Symfony\Component\Ldap\Entry|false $eds_addr_practice
 * @param \Symfony\Component\Ldap\Entry|false $eds_dept
 * @param array|false $profile
 * @return array
 */
function hook_ucsf_eds_profiles_node_sync_pre_save(array $values, NodeInterface $node, Entry $eds, $eds_addr_campus, $eds_addr_practice, $eds_dept, $profile) {
  $values['field_machine_name'] = 'new value';
  return $values;
}
