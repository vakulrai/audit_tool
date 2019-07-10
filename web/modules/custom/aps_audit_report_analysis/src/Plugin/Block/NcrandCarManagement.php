<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'NcrandCarManagement' block.
 *
 * @Block(
 *  id = "ncrand_car_management",
 *  admin_label = @Translation("Ncrand car management"),
 * )
 */
class NcrandCarManagement extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $build['#cache']['max-age'] = 0;
  	$current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
  	$data = '';
    $build = [];
    $build['audit_ncr_car_report']['ncr_car'] = [
      '#type' => 'fieldset',
    ];
    $build['audit_ncr_car_report']['ncr_car']['container_element_ncr_car']['#markup'] = '<div id="container-element-ncr-car" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>';
    $build['audit_ncr_car_report']['#attached']['library'][] = 'aps_audit_report_analysis/aps_dashboard_ncr_car_js';
    $build['#attached']['drupalSettings']['ncr_car_data']['total'] = count(getAuditOPtions('ncr','/ncr-car-management-details/'.$uri[1].'?type=auditor_report'));
    $build['#attached']['drupalSettings']['ncr_car_data']['completed'] = count(getAuditOPtions('ncr','/ncr-car-management-details/'.$uri[1].'?type=auditor_report&moderation_state=workflow_for_audit_planning-submit_audit'));
    $build['#attached']['drupalSettings']['ncr_car_data']['pending'] = count(getAuditOPtions('ncr','/ncr-car-management-details/'.$uri[1].'?type=auditor_report&moderation_state=workflow_for_audit_planning-post_audit'));

    return $build;
  }

}
