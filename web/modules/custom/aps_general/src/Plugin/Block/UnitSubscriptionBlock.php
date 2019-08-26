<?php

namespace Drupal\aps_general\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'UnitSubscriptionBlock' block.
 *
 * @Block(
 *  id = "unit_subscription_block",
 *  admin_label = @Translation("Unit subscription block"),
 * )
 */
class UnitSubscriptionBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
  	$route_name = \Drupal::routeMatch()->getRouteName();
  	$node = \Drupal::routeMatch()->getParameter('node');
  	if ($node instanceof \Drupal\node\NodeInterface) {
      $nid_type = $node->bundle();
      $nid = $node->id();
    }
  	if($route_name == 'entity.node.canonical' && $nid_type == 'unit'){
  	  $build['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $build['subscription'] = [
	    '#type' => 'link',
	    '#title' => t('License Expires in 6 Months'),
	    '#url' => Url::fromRoute('aps_general.get_subscription',['id' => $nid]),
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
