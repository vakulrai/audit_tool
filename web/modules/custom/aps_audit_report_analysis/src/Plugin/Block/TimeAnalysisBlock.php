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
    $build['#cache']['max-age'] = 0;
    $build['#markup'] = 'Time Analysis';
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $event_id = $uri[1];
    $report_id = $uri[3];
    $node_object = Node::load($report_id);
    $timestamp_CAR = $node_object->changed->value;
    $ids_to_fetch = [$event_id, $report_id];
    foreach ($ids_to_fetch as $key) {
      $get_planned_event_created_time[] = $this->getCreationDate($key);
    }
    $diff = $get_planned_event_created_time[1][0]->created - $get_planned_event_created_time[0][0]->created;
    $days = floor($diff/(60*60*24));
    $hours = round($diff/3600, 1);

    $diff_car = $timestamp_CAR - $get_planned_event_created_time[1][0]->created;
    $days_car = floor($diff_car/(60*60*24));
    $hours_car = round($diff_car/3600, 1);
    /*Effeciecy Logic 
     * Considering current Turnover is > 10 cr. so, no of mandays = 4 * 8.
     */

    $man_DAYS = 4;
    $audit_day = round($hours / 8);
    $car_day = round($hours_car / 8); 

    if($man_DAYS > $audit_day){
      $audit_TIME = $audit_day/$man_DAYS *100;
    }
    elseif($man_DAYS < $audit_day){
      $audit_TIME = 100 - $man_DAYS/$audit_day * 100;
    }
    if($man_DAYS > $car_day){
      $car_TIME =  $car_day/$man_DAYS  * 100;
    }
    elseif($man_DAYS < $car_day){
      $car_TIME = $car_day/$man_DAYS * 100 - 100;
    }

    $audit_EFFECIENCY = round($audit_TIME, 2);
    $car_EFFECIENCY = round($car_TIME, 2);

    $audit_HOURS = $audit_day;
    $car_HOURS = $car_day;

    $build['#audit_effeciency'] = $audit_EFFECIENCY;
    $build['#car_effeciency'] = $car_EFFECIENCY;
    $build['#car_hours'] = $car_HOURS;
    $build['#audit_hours'] = $audit_HOURS;
    $build['#theme'] = 'time_analysis_block';
    $build['#cache']['max-age'] = 0;
    return $build;
  }

  public function getCreationDate($id){
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->fields('n',['nid', 'type', 'created']);
    $query->condition('n.nid', $id);
    $data = $query->execute()->fetchAll();
    return $data;
  }

}
