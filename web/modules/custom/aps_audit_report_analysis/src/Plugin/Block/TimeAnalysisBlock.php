<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
/**
 * Provides a 'TimeAnalysisBlock' block.
 *
 * @Block(
 *  id = "time_analysis_block",
 *  admin_label = @Translation("Time analysis block"),
 * )
 */
class TimeAnalysisBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#markup'] = 'Time Analysis';
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $event_id = $uri[1];
    $report_id = $uri[3];
    $node_object = Node::load($report_id);
    $event_object = Node::load($event_id);
    $timestamp_CAR = $node_object->created->value;
    $ids_to_fetch = [$event_id, $report_id];
    
    $get_planned_event_created_time[] = Node::load($event_id)->get('field_start_date')->value;
    $get_planned_event_created_time[] = getCreationDatefromRevision($event_id, 'submit_audit');
   
    $date_of_audit = new \DateTime(date('Y-m-d H:i:s',$get_planned_event_created_time[0]));
    $audit_submission = new \DateTime(date('Y-m-d H:i:s',$get_planned_event_created_time[1][0]->revision_timestamp));

    $diff = $date_of_audit->diff($audit_submission);
    $check_invert = $diff->invert;

    $car_start_date = new \DateTime(date('Y-m-d H:i:s',$timestamp_CAR));
    $car_end_date = new \DateTime(date('Y-m-d H:i:s',getCreationDatefromRevision($report_id,'submit_audit')[0]->revision_timestamp));

    $car_diff = $car_start_date->diff($car_end_date);
    $check_car_invert = $car_diff->invert;

    //***  Check Mandays form UNIT  ***//
    if(isset($event_object->field_refere->target_id)){
      $unit_reference = $event_object->field_refere->target_id;
      $unit_object = Node::load($unit_reference);
      if($event_object->field_audit_type->value == 'internal'){
        switch ($event_object->field_internal_audit_type->value) {
          case 'systems':
            $unit_field_sales_turnover = $unit_object->field_sales_turnover->value;
            $unit_field_contributed_sales = $unit_object->field_contributed_sales->value;
            $unit_field_total_members = $unit_object->field_total_members->value;
            $mandays_count = getMandaysFromAuditType('systems', $unit_field_sales_turnover, $unit_field_contributed_sales, $unit_field_total_members);
          break;

          case 'process':
            $get_total_process_assembly = count(getAuditOPtions('process','/rest-export-system/'.$unit_reference.'?type[]=assembly'));
            $get_total_process_manufacturing = count(getAuditOPtions('process','/rest-export-system/'.$unit_reference.'?type[]=manufacturing_process'));
            $mandays_count = getMandaysFromAuditType('process', $get_total_process_manufacturing, $get_total_process_assembly, NULL);
          break;

          case 'product':
            $unit_reference = $node_object->field_refere->target_id;
            $query = \Drupal::database()->select('customer_manual_parts__field_reference_id', 'cm');
            $query->fields('cm',['field_reference_id_target_id', 'entity_id']);
            $query->condition('cm.field_reference_id_target_id', $unit_reference);
            $nids = $query->execute()->fetchAll();
            $get_total_product = count($nids);
            $mandays_count = getMandaysFromAuditType('product', $get_total_product, NULL, NULL);
          break;
            
          default:
            $mandays_count = 4;
          break;
        }
      }
    }
    /*Effeciecy Logic 
     * Considering current Turnover is > 10 cr. so, no of mandays = 4 * 8.
     */

    if($check_invert != 1){
      $man_DAYS = $mandays_count * 8;
      $total_hours = $diff->d * 24 + $diff->h + ($diff->i / 60); 
      $total_days = $total_hours / 8;

      if($total_days > $man_DAYS){
        $audit_TIME = 0;
      }
      elseif($total_days < $man_DAYS){
        $time = $total_days/$man_DAYS * 100;
        if($time >= 100){
          $audit_TIME = 100;
        }
        else{
          $audit_TIME = $time;
        }
      }

      if($check_car_invert != 1){
        $total_car_hours = $car_diff->d * 24 + $car_diff->h + $car_diff->i / 60;
        $total_car_days = $total_car_hours / 8;
        if($total_car_days > $man_DAYS){
          $car_TIME =  0;
        }
        elseif($total_car_days < $man_DAYS){
          $time_car = $total_car_days/$man_DAYS * 100;
          if($time_car > 100){
          $car_TIME = 100;
        }
        else{
          $car_TIME = $time_car;
        }
        }
      }

      $audit_EFFECIENCY = round($audit_TIME, 2);
      $car_EFFECIENCY = round($car_TIME, 2);

      $audit_HOURS = $audit_day;
      $car_HOURS = $car_day;
    }
    else{
      $audit_EFFECIENCY = 'N/A<br>Submission date has been Passed.';
      $car_EFFECIENCY = 'N/A';

      $audit_HOURS = 'N/A';
      $car_HOURS = 'N/A';
    }
  
    $build['#audit_effeciency'] = $audit_EFFECIENCY;
    $build['#car_effeciency'] = $car_EFFECIENCY;
    $build['#car_hours'] = round($total_car_days, 2). '* 8 = '.round($total_car_days * 8, 2);
    $build['#audit_hours'] = round($total_days, 2) .'* 8 = '.round($total_days * 8, 2);
    $build['#mandays'] = $mandays_count .'* 8  ^(1 Manday = 8)';
    $build['#theme'] = 'time_analysis_block';
    $build['#cache']['max-age'] = 0;
    return $build;
  }

}
