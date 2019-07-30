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
  	$current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $build = [];
    $build['#markup'] = '<h1>Coverage</h1>';
    $header = [
      $this->t('Audit.'),
      $this->t('Audit Type'),
      $this->t('Completed'),
      $this->t('on-going'),
      $this->t('Total'),
    ];
    $build['tableselect_element'] = [
      '#type' => 'table',
      '#header' => $header,
      '#prefix' => '<div class=table-responsive-wrapper>',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['table-responsive']],
      '#empty' => t('No content available.'),
    ];
    $data = $this->getAuditDetails($uri[1]);

    if (count($data)) {
    	$sr = 1;
	    foreach ($data as $key => $value) {
        foreach ($value as $data_key) {
	        $build['tableselect_element'][$sr]['audit_type'] = [
	          '#markup' => strtoupper($key),
	          '#title_display' => 'invisible',
            '#wrapper_attributes' => ['data-label' => 'Audit.'],
	        ];

	        $build['tableselect_element'][$sr]['type'] = [
	          '#markup' => $data_key['type'] ? $data_key['type'] : '-',
	          '#title_display' => 'invisible',
            '#wrapper_attributes' => ['data-label' => 'Audit Type'],
	        ];

	        $build['tableselect_element'][$sr]['completed'] = [
	          '#markup' => $data_key['completed']? $data_key['completed'] : '-',
	          '#title_display' => 'invisible',
            '#wrapper_attributes' => ['data-label' => 'Completed'],
	        ];

	        $build['tableselect_element'][$sr]['on_going'] = [
	          '#markup' => $data_key['on-going']? $data_key['on-going'] : '-',
	          '#title_display' => 'invisible',
            '#wrapper_attributes' => ['data-label' => 'on-going'],
	        ];

	        $build['tableselect_element'][$sr]['count'] = [
	          '#markup' => $data_key['count']? $data_key['count'] : '-',
	          '#title_display' => 'invisible',
            '#wrapper_attributes' => ['data-label' => 'Total'],
	        ];
	        $sr++;
        }
      }
	}
    $build['#cache']['max-age'] = 0;
    return $build;
  }

  public function getAuditDetails($unit_reference){
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->join('node__field_refere', 'rf', 'n.nid = rf.entity_id');
    $query->join('content_moderation_state_field_data', 'cm', 'rf.entity_id = cm.content_entity_id');
    $query->fields('n',['nid', 'title', 'created']);
    $query->fields('rf',['field_refere_target_id']);
    $query->fields('cm',['revision_id', 'moderation_state']);
    $query->condition('n.type', 'planned_events');
    $query->condition('rf.field_refere_target_id', $unit_reference);
    $data = $query->execute()->fetchAll();

    $audit_data = [];
    $count_complete_system = 1;
    $count_ongoing_system = 1;
    $count_complete_process = 1;
    $count_ongoing_process = 1;
    $count_complete_product = 1;
    $count_ongoing_product = 1;
    $count_complete_external = 1;
    $count_ongoing_external = 1;
    $count_complete_supplier = 1;
    $count_ongoing_supplier = 1;
    $count_complete_ia = 1;
    $count_ongoing_ia = 1;
    $count_complete_ea = 1;
    $count_ongoing_ea = 1;
    foreach ($data as $key => $value) {
      $node_object = Node::load($value->nid);
      if($node_object->field_audit_type->value == 'internal' && $node_object->field_internal_audit_type->value == 'systems'){
        $audit_data['internal'][$node_object->field_internal_audit_type->value]['type'] = $node_object->field_internal_audit_type->value;
        if($value->moderation_state == 'submit_audit'  || $value->moderation_state == 'post_audit'){
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['completed'] = $count_complete_system;
          $count_complete_system++;
        }
        elseif ($value->moderation_state == 'scheduled' || $value->moderation_state == 'reschedule') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['on-going'] = $count_ongoing_system;
          $count_ongoing_system++;
        }
        $count_system[] = $value->nid;
        $audit_data['internal'][$node_object->field_internal_audit_type->value]['count'] = count($count_system);
      }
      elseif ($node_object->field_audit_type->value == 'internal' && $node_object->field_internal_audit_type->value == 'process') {
        $audit_data['internal'][$node_object->field_internal_audit_type->value]['type'] = $node_object->field_internal_audit_type->value;
        if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['completed'] = $count_complete_process;
          $count_complete_process++;
        }
        elseif ($value->moderation_state == 'scheduled' || $value->moderation_state == 'reschedule') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['on-going'] = $count_ongoing_process;
          $count_ongoing_process++;
        }
        $count_process[] = $value->nid;
        $audit_data['internal'][$node_object->field_internal_audit_type->value]['count'] = count($count_process);
      }
      elseif ($node_object->field_audit_type->value == 'internal' && $node_object->field_internal_audit_type->value == 'product') {
      	$audit_data['internal'][$node_object->field_internal_audit_type->value]['type'] = $node_object->field_internal_audit_type->value;
      	if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['completed'] = $count_complete_product;
          $count_complete_product++;
        }
        elseif ($value->moderation_state == 'scheduled' || $value->moderation_state == 'reschedule') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['on-going'] = $count_ongoing_product;
          $count_ongoing_product++;
        }
        $count_product[] = $value->nid;
        $audit_data['internal'][$node_object->field_internal_audit_type->value]['count'] = count($count_product);
      }
      elseif ($node_object->field_audit_type->value == 'external') {
      	$ids_[] = $value->nid;
      	$audit_data['external'][$node_object->field_audit_type->value]['type'] = $node_object->field_audit_type->value;
      	if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['external'][$node_object->field_audit_type->value]['completed'] = $count_complete_external;
          $count_complete_external++;
        }
        elseif ($value->moderation_state == 'scheduled' || $value->moderation_state == 'reschedule') {
          $audit_data['external'][$node_object->field_audit_type->value]['on-going'] = $count_ongoing_external;
          $count_ongoing_external++;
        }
        $count_external[] = $value->nid;
        $audit_data['external'][$node_object->field_audit_type->value]['count'] = count($count_external);
      }
      elseif ($node_object->field_audit_type->value == 'supplier') {
      	$audit_data['supplier'][$node_object->field_audit_type->value]['type'] = $node_object->field_audit_type->value;
      	if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['supplier'][$node_object->field_audit_type->value]['completed'] = $count_complete_supplier;
          $count_complete_supplier++;
        }
        elseif ($value->moderation_state == 'scheduled' || $value->moderation_state == 'reschedule') {
          $audit_data['supplier'][$node_object->field_audit_type->value]['on-going'] = $count_ongoing_supplier;
          $count_ongoing_supplier++;
        }
        $count_supplier[] = $value->nid;
        $audit_data['supplier'][$node_object->field_audit_type->value]['count'] = count($count_supplier);
      }
      elseif ($node_object->field_audit_type->value == 'customer' && $node_object->field_customer_type->value == 'internal_assessment') {
      	$audit_data['customer'][$node_object->field_customer_type->value]['type'] = $node_object->field_customer_type->value;
      	if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['customer'][$node_object->field_customer_type->value]['completed'] = $count_complete_ia;
          $count_complete_ia++;
        }
        elseif ($value->moderation_state == 'scheduled' || $value->moderation_state == 'reschedule') {
          $audit_data['customer'][$node_object->field_customer_type->value]['on-going'] = $count_ongoing_ia;
          $count_ongoing_ia++;
        }
        $count_product[] = $value->nid;
        $audit_data['customer'][$node_object->field_customer_type->value]['count'] = count($count_product);
      }
      elseif ($node_object->field_audit_type->value == 'customer' && $node_object->field_customer_type->value == 'external_assessment') {
      	$audit_data['customer'][$node_object->field_customer_type->value]['type'] = $node_object->field_customer_type->value;
      	if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['customer'][$node_object->field_customer_type->value]['completed'] = $count_complete_ea;
          $count_complete_ea++;
        }
        elseif ($value->moderation_state == 'scheduled' || $value->moderation_state == 'reschedule') {
          $audit_data['customer'][$node_object->field_customer_type->value]['on-going'] = $count_ongoing_ea;
          $count_ongoing_ea++;
        }
        $count_product[] = $value->nid;
        $audit_data['customer'][$node_object->field_customer_type->value]['count'] = count($count_product);
      }
    }
    return $audit_data;
  }

}
