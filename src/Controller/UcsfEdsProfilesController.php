<?php

namespace Drupal\ucsf_eds_profiles\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UcsfEdsProfilesController extends ControllerBase {

	use MessengerTrait;

  public function update_eds_node(NodeInterface $node) {
  	$nid = $node->id();

  	if($node->bundle() == 'ucsf_eds_profiles') {
  		$updated = ucsf_eds_profiles_node_sync($node);
  		if($updated) {
        $this->messenger()->addMessage('Updated.');
      } else {
        $this->messenger()->addMessage('No change.');
      }
  	}

  	if($node->bundle() != 'ucsf_eds_profiles') {
	  	$this->messenger()->addMessage('Nothing happened - update EDS only works on UCSF EDS nodes.');
  	}

	  $response = new RedirectResponse('/node/'.$nid);
	  return $response->send();
  }

}