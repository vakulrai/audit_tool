<?php

namespace Drupal\aps_audit_report_analysis\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller routines for aps_audit_report_analysis routes.
 */
class GetAuditCoverageDetails extends ControllerBase {

  public function getAuditDetails() {
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    if(isset($_REQUEST['unit_reference'])){
      $unit_reference = $_REQUEST['unit_reference'];
    }else{
      $unit_reference = $uri[1];
    }
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->join('content_moderation_state_field_data', 'cm', 'n.nid = cm.content_entity_id');
    if(isset($_REQUEST['audit_type']) && $_REQUEST['audit_type'] != 'all'){
      $check = $_REQUEST['audit_type'];
      $query->join('node__field_audit_type', 'at', 'cm.content_entity_id = at.entity_id');
      $query->fields('at',['field_audit_type_value']);
    }
    $query->join('node__field_refere', 'rf', 'cm.content_entity_id = rf.entity_id');
    $query->fields('n',['nid', 'title', 'created']);
    $query->fields('rf');
    $query->fields('cm',['revision_id', 'moderation_state']);
    if(isset($check)){
      $query->condition('at.field_audit_type_value', $check);
    }
    $query->condition('n.type', 'planned_events');
    $query->condition('rf.field_refere_target_id', $unit_reference);
    $data = $query->execute()->fetchAll();

    $audit_data = [];
    $count_complete_system = 1;
    $count_ongoing_system = 1;
    $count_rescheduled_system = 1;

    $count_complete_process = 1;
    $count_ongoing_process = 1;
    $count_rescheduled_process = 1;

    $count_complete_product = 1;
    $count_ongoing_product = 1;
    $count_rescheduled_product = 1;

    $count_complete_external = 1;
    $count_ongoing_external = 1;
    $count_rescheduled_external = 1;

    $count_complete_supplier = 1;
    $count_ongoing_supplier = 1;
    $count_rescheduled_supplier = 1;

    $count_complete_ia = 1;
    $count_ongoing_ia = 1;
    $count_rescheduled_ia = 1;

    $count_complete_ea = 1;
    $count_ongoing_ea = 1;
    $count_rescheduled_ea = 1;
    foreach ($data as $key => $value) {
      $node_object = Node::load($value->nid);
      if($node_object->field_audit_type->value == 'internal' && $node_object->field_internal_audit_type->value == 'systems'){
        $audit_data['internal'][$node_object->field_internal_audit_type->value]['type'] = $node_object->field_internal_audit_type->value;
        if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['completed'] = $count_complete_system;
          $count_complete_system++;
        }
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['on-going'] = $count_ongoing_system;
          $count_ongoing_system++;
        }
        elseif ($value->moderation_state == 'reschedule') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['reschedule'] = $count_rescheduled_system;
          $count_rescheduled_system++;
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
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['on-going'] = $count_ongoing_process;
          $count_ongoing_process++;
        }
        elseif ($value->moderation_state == 'reschedule') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['reschedule'] = $count_rescheduled_process;
          $count_rescheduled_process++;
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
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['on-going'] = $count_ongoing_product;
          $count_ongoing_product++;
        }
        elseif ($value->moderation_state == 'reschedule') {
          $audit_data['internal'][$node_object->field_internal_audit_type->value]['reschedule'] = $count_rescheduled_product;
          $count_rescheduled_product++;
        }
        $count_product[] = $value->nid;
        $audit_data['internal'][$node_object->field_internal_audit_type->value]['count'] = count($count_product);
      }
      elseif ($node_object->field_audit_type->value == 'external') {
        $ids_[] = $value->nid;
        $audit_data['external'][$node_object->field_audit_type->value]['type'] = $node_object->field_internal_audit_type->value;
        if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['external'][$node_object->field_audit_type->value]['completed'] = $count_complete_external;
          $count_complete_external++;
        }
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['external'][$node_object->field_audit_type->value]['on-going'] = $count_ongoing_external;
          $count_ongoing_external++;
        }
        elseif ($value->moderation_state == 'reschedule') {
          $audit_data['external'][$node_object->field_audit_type->value]['reschedule'] = $count_rescheduled_external;
          $count_rescheduled_external++;
        }
        $count_external[] = $value->nid;
        $audit_data['external'][$node_object->field_audit_type->value]['count'] = count($count_external);
      }
      elseif ($node_object->field_audit_type->value == 'supplier') {
        $audit_data['supplier'][$node_object->field_audit_type->value]['type'] = $node_object->field_internal_audit_type->value;
        if($value->moderation_state == 'submit_audit'|| $value->moderation_state == 'post_audit'){
          $audit_data['supplier'][$node_object->field_audit_type->value]['completed'] = $count_complete_supplier;
          $count_complete_supplier++;
        }
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['supplier'][$node_object->field_audit_type->value]['on-going'] = $count_ongoing_supplier;
          $count_ongoing_supplier++;
        }
        elseif ($value->moderation_state == 'reschedule') {
          $audit_data['supplier'][$node_object->field_audit_type->value]['reschedule'] = $count_rescheduled_supplier;
          $count_rescheduled_supplier++;
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
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['customer'][$node_object->field_customer_type->value]['on-going'] = $count_ongoing_ia;
          $count_ongoing_ia++;
        }
        elseif ($value->moderation_state == 'reschedule') {
          $audit_data['customer'][$node_object->field_customer_type->value]['reschedule'] = $count_rescheduled_ia;
          $count_rescheduled_ia++;
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
        elseif ($value->moderation_state == 'scheduled') {
          $audit_data['customer'][$node_object->field_customer_type->value]['on-going'] = $count_ongoing_ea;
          $count_ongoing_ea++;
        }
        elseif ($value->moderation_state == 'reschedule') {
          $audit_data['customer'][$node_object->field_customer_type->value]['reschedule'] = $count_rescheduled_ea;
          $count_rescheduled_ea++;
        }
        $count_product[] = $value->nid;
        $audit_data['customer'][$node_object->field_customer_type->value]['count'] = count($count_product);
      }
    }

    $count_result = 0;
    $count_completed = 0;
    $count_pending = 0;
    $count_reschedule = 0;
    $plannig_report_json = [];
    $total_count = 0;
    foreach ($audit_data as $i => $j) {
      foreach ($j as $val) {
        if($val['reschedule']){  
          $count_reschedule += $val['reschedule'];   
        }
        if($val['on-going']){
          $count_pending += $val['on-going'];
        }
        if($val['completed']){ 
          $count_completed += $val['completed'];
        }
        if($val['post_audit']){ 
          $$count_completed += $val['post_audit'];
        }
        $count_result++;
      }
    }
    
    if($count_reschedule == 0 && $count_pending == 0 && $count_completed == 0){
      $plannig_report_json;
    }else{
      $plannig_report_json[0]['name'] = strtoupper('reschedule');
      $plannig_report_json[0]['y'] = $count_reschedule;
      $plannig_report_json[0]['color'] = 'red';
      $plannig_report_json[1]['name'] = strtoupper('on going');
      $plannig_report_json[1]['y'] = $count_pending;
      $plannig_report_json[2]['name'] = strtoupper('completed');
      $plannig_report_json[2]['y'] = $count_completed;
    }

    return new JsonResponse( $plannig_report_json );
  }
}
