<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
    $ids_to_fetch = [$event_id, $report_id];
    foreach ($ids_to_fetch as $key) {
      $get_planned_event_created_time[] = $this->getCreationDate($key);
    }
    $diff = $get_planned_event_created_time[1][0]->created - $get_planned_event_created_time[0][0]->created;
    $years = floor($diff / (365*60*60*24)); 
    $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
    $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));  
    $hours = floor(($diff - $years * 365*60*60*24  - $months*30*60*60*24 - $days*60*60*24) / (60*60));  

    /*Effeciecy Logic 
     * Considering current Turnover is > 10 cr. so, no of mandays = 4 * 8.
     */
    $audit_TIME = $days;
    $audit_EFFECIENCY = round((32 / $audit_TIME * 8), 2);
    $car_EFFECIENCY = round((32 / $audit_TIME * 8), 2);
    $audit_HOURS = $hours;
    $car_HOURS = $hours;
    $build['#audit_effeciency'] = $audit_EFFECIENCY;
    $build['#car_effeciency'] = $car_EFFECIENCY;
    $build['#car_hours'] = $car_HOURS;
    $build['#audit_hours'] = $audit_HOURS;
    $build['#theme'] = 'time_analysis_block';
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
