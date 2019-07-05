<?php

namespace Drupal\aps_audit_planning\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'AuditPlanningBlock' block.
 *
 * @Block(
 *  id = "audit_planning_block",
 *  admin_label = @Translation("Audit planning block"),
 * )
 */
class AuditPlanningBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
  	global $base_url;
    $build['#cache']['max-age'] = 0;
    $build = [];
    $build['audit_planning_block']['#markup'] = '<div id="demo"></div>';
    $build['#attached']['library'][] = 'aps_audit_planning/aps_audit_planning_js';
    if (\Drupal::routeMatch()->getRouteName() == 'aps_audit_planning.audit_planning_controller_build') {
      $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
      $uri = explode('/', $current_uri);
      $build['#attached']['drupalSettings']['unitId'] = $uri[1];
    }  
    $build['#attached']['drupalSettings']['siteBaseUrl'] = $base_url;
    return $build;
  }

}
