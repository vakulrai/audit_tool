<?php

namespace Drupal\aps_general\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'UpdateFunctions' block.
 *
 * @Block(
 *  id = "update_functions",
 *  admin_label = @Translation("Update functions"),
 * )
 */
class UpdateFunctions extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
  	$current_user = \Drupal::currentUser();
    $current_user_id = $current_user->id();
    $roles = $current_user->getRoles();
  	foreach ($roles as $key => $value) {
  	  $user_role = $value;
  	}
  	$route_name = \Drupal::routeMatch()->getRouteName();
    if($route_name == 'view.user_listing.auditor_score' && $user_role == 'auditor'){
  	  $build['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $build['update_score'] = [
	    '#type' => 'link',
	    '#title' => t('Update Functions'),
	    '#url' => Url::fromRoute('aps_general.update_score',['id' => $nid]),
	    '#attributes' => [
	      'class' => [
	        'use-ajax',
	        'button',
	      ],
	    ],
	  ];
  	}
    return $build;
  }

}
