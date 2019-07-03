<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'MradminDashboardCoverage' block.
 *
 * @Block(
 *  id = "mradmin_dashboard_coverage",
 *  admin_label = @Translation("Mradmin dashboard coverage"),
 * )
 */
class MradminDashboardCoverage extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
  	$data = $this->getAuditDetails();
  	// echo '<pre>';print_r($data);
    $build = [];
    $build['#markup'] = 'Coverage';
    $build['#data'] = $audit_HOURS;
    $build['#theme'] = 'mradmin_dashboard_coverage';

    return $build;
  }

  public function getAuditDetails(){
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->join('content_moderation_state_field_data', 'cm', 'n.nid = cm.content_entity_id');
    $query->fields('n',['nid', 'title', 'created']);
    $query->fields('cm',['revision_id', 'moderation_state']);
    $query->condition('n.type', 'planned_events');
    $data = $query->execute()->fetchAll();
    $audit_data = [];
    $count_complete = 1;
    $count_ongoing = 1;
    foreach ($data as $key => $value) {
      $node_object = Node::load($value->nid);
      if($node_object->field_internal_audit_type->value == 'systems'){
        $audit_data['internal']['system']['type'] = $node_object->field_internal_audit_type->value;
        if($value->moderation_state == 'submit_audit'){
          $audit_data['internal']['system']['completed'] = $count_complete;
          $count_complete++;
        }
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['internal']['system']['on-going'] = $count_ongoing;
          $count_ongoing++;
        }
        $count_system[] = $value->nid;
        $audit_data['internal']['system']['count'] = count($count_system);
      }
      elseif ($node_object->field_internal_audit_type->value == 'process') {
        $audit_data['internal']['process']['type'] = $node_object->field_internal_audit_type->value;
        if($value->moderation_state == 'submit_audit'){
          $audit_data['internal']['process']['completed'] = $count;
          $count_complete++;
        }
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['internal']['process']['on-going'] = $count_ongoing;
          $count_ongoing++;
        }
        $count_process[] = $value->nid;
        $audit_data['internal']['process']['count'] = count($count_process);
      }
      elseif ($node_object->field_internal_audit_type->value == 'product') {
      	$audit_data['internal']['product']['type'] = $node_object->field_internal_audit_type->value;
      	if($value->moderation_state == 'submit_audit'){
          $audit_data['internal']['product']['completed'] = $count;
          $count_complete++;
        }
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['internal']['product']['on-going'] = $count_ongoing;
          $count_ongoing++;
        }
        $count_product[] = $value->nid;
        $audit_data['internal']['product']['count'] = count($count_product);
      }
    }
    return $audit_data;
  }

}
