<?php
namespace Drupal\ucsf_eds_profiles\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Synchronizes EDS and Profiles nodes with data source.
 *
 * @QueueWorker(
 *   id = "ucsf_eds_profiles_sync",
 *   title = @Translation("Synchronizes EDS and Profiles nodes with data source."),
 *   cron = {"time" = 60}
 * )
 */
class UcsfEdsProfilesSyncQueue extends QueueWorkerBase {
  /**
   * {@inheritdoc}
   */
  public function processItem($node) {
    $updated = ucsf_eds_profiles_node_sync($node);
  }
}