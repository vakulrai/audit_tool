<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'AuditFindingControlScheduled' block.
 *
 * @Block(
 *  id = "audit_finding_control_scheduled",
 *  admin_label = @Translation("Audit finding control scheduled"),
 * )
 */
class AuditFindingControlScheduled extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
  	global $base_url;
    $build['#cache']['max-age'] = 0;
  	$current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $data = $this->completionRate($uri[1]);
  	$data = '';
    $build = [];
    $build['audit_finding_control_scheduled']['fieldset'] = [
      '#type' => 'fieldset',
    ];
    $build['audit_finding_control_scheduled']['fieldset']['container_element_scheduled_a']['#markup'] = '<div id="container-scheduled-a" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div><a href="/internal-audit-product?unit_reference='.$uri[1].'" id="product-list">view list</a>';
    $build['audit_finding_control_scheduled']['fieldset']['container_element_scheduled_b']['#markup'] = '<div id="container-scheduled-b" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div><a href="/internal-audit-process?unit_reference='.$uri[1].'" id="process-list">view list</a>';
    $build['audit_finding_control_scheduled']['fieldset']['container_element_scheduled_c']['#markup'] = '<div id="container-scheduled-c" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div><a href="/procedure-listing?unit_reference='.$uri[1].'" id="procedure-list">view list</a>';
    $build['audit_finding_control_scheduled']['#attached']['library'][] = 'aps_audit_report_analysis/aps_dashboard_audit_control_scheduled_js';

    $build['#attached']['drupalSettings']['product_data']['total'] = count(getAuditOPtions('product','/rest-export-system/'.$uri[1].'?content_type=customers_manual'));
    $build['#attached']['drupalSettings']['product_data']['completed'] = count(getAuditOPtions('product','/rest-export-system/'.$uri[1].'?content_type=customers_manual&moderation_type=workflow_for_audit_planning-submit_audit'));

    $build['#attached']['drupalSettings']['process_data']['total'] = count(getAuditOPtions('process','/rest-export-system/'.$uri[1].'?content_type[]=assembly&content_type[]=manufacturing_process'));
    $build['#attached']['drupalSettings']['process_data']['completed'] = count(getAuditOPtions('process','/rest-export-system/'.$uri[1].'?content_type[]=assembly&content_type[]=manufacturing_process&moderation_type=workflow_for_audit_planning-submit_audit'));

    $build['#attached']['drupalSettings']['procedure_data']['total'] = count(getAuditOPtions('process','/rest-export-system/'.$uri[1].'?content_type[]=procedures'));
    $build['#attached']['drupalSettings']['procedure_data']['completed'] = count(getAuditOPtions('process','/rest-export-system/'.$uri[1].'?content_type[]=procedures&moderation_type=workflow_for_audit_planning-submit_audit'));
    $build['#attached']['drupalSettings']['siteBaseUrl'] = $base_url;
    $build['#attached']['drupalSettings']['data'] = $data;
    $build['#attached']['drupalSettings']['unit_reference'] = $uri[1];
    return $build;
  }

  public function completionRate($unit_reference){
    $query = \Drupal::database()->select('content_moderation_state_field_data', 'cm');
    $query->join('node_field_revision', 'rf', 'cm.content_entity_revision_id = rf.vid');
    $query->join('node__field_refere', 'ref', 'cm.content_entity_id = ref.entity_id');
    $query->fields('rf',['nid', 'vid', 'changed']);
    $query->fields('cm',['revision_id', 'moderation_state']);
    $query->fields('ref');
    $query->condition('ref.bundle', 'planned_events');
    $query->condition('cm.moderation_state', 'submit_audit');
    $query->condition('ref.field_refere_target_id', $unit_reference);
    $nids = $query->execute()->fetchAll();
    return $nids;
  }

}
