<?php

namespace Drupal\aps_audit_planning\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class AuditPlanningController.
 */
class AuditPlanningController extends ControllerBase {

  /**
   * Build.
   *
   * @return string
   *   Return Hello string.
   */
  public function build() {
    return [
      '#type' => 'markup',
      '#title' => $this->t('Audit Planning Calendar')
    ];
  }

  public function updateKPI() {
    $get_kpi_data = $_REQUEST;
    if (count($get_kpi_data)) {
      if($get_kpi_data['type'] == 'record'){
        try {
           $node_object = Node::load($get_kpi_data['record_reference']);
           $node_object->set('field_kpi_status', $get_kpi_data['value_selected']);
           $node_object->save();
           $respose['response'] = TRUE;
        } 
        catch(\Exception $e) {
          $respose['response'] = FALSE;
        }
      }
      else{
         try {
           $paragraphs_answer_object = Paragraph::load($get_kpi_data['record_reference']);
           $paragraphs_answer_object->set('field_car_status', $get_kpi_data['value_selected']);
           $paragraphs_answer_object->save();
           $respose['response'] = 'Updated';

        } 
        catch(\Exception $e) {
          $respose['response'] = 'Failed';
        }
      }
    }
    return new JsonResponse( $response );
  }

  public function getPressureMonths(Request $request, $unit_reference) {
    if($unit_reference){
      $unit_object = Node::load($unit_reference);
      $first_last_date_monthly = [];
      $i = 0;

      foreach ($unit_object->field_field_months_for_the_audit->getValue() as $key => $value) {
        $num_padded = sprintf("%02d", $value['value']);
        $format_for_first_day = 'Y-'. $num_padded . '-01';
        $format_for_last_day = 'Y-'.$num_padded.'-t';
        $first_last_date_monthly[$i]['id'] = $num_padded;
        $first_last_date_monthly[$i]['start'] = date($format_for_first_day);
        $first_last_date_monthly[$i]['end'] = date($format_for_last_day);
        $first_last_date_monthly[$i]['rendering'] = 'background';
        $first_last_date_monthly[$i]['color'] = '#7048e8';
        $first_last_date_monthly[$i]['description'] = 'Pressure Months';
        $first_last_date_monthly[$i]['className'] = 'pressuire';
        $i++;
      }
      $data  = $first_last_date_monthly;
      return new JsonResponse($data);
    }
  }

  public function verifyPressureMonths(Request $request, $unit_reference, $month) {
    if($unit_reference){
      $check_months =[];
      $unit_object = Node::load($unit_reference);
      foreach ($unit_object->field_field_months_for_the_audit->getValue() as $key => $value) {
        $check_months[$key] = $value['value'];
      }
      if (in_array($month, $check_months)) {
        $response = TRUE;
      }
      else{
        $response = FALSE;
      }
      return new JsonResponse($response);
    }
  }

  public function verifyGoogleHolidays(Request $request, $unit_reference, $day) {
    $xml = simplexml_load_file("https://calendar.google.com/calendar/embed?src=en.indian%23holiday%40group.v.calendar.google.com&ctz=Asia%2FKolkata");
    $xml->asXML();
    $holidays = array();
    foreach ($xml->entry as $entry){
      $a = $entry->children('http://schemas.google.com/g/2005');
      $when = $a->when->attributes()->startTime;

      $holidays[(string)$when]["title"] = $entry->title;
    }
      return new JsonResponse($holidays);
  }

  public function generateEvents(Request $request, $unit_reference) {
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->join('node__field_start_date', 'st', 'n.nid = st.entity_id');
    $query->join('node__field_end_date', 'ed', 'st.entity_id = ed.entity_id');
    $query->join('node__field_refere', 'rf', 'n.nid = rf.entity_id');
    $query->fields('n',['title', 'nid']);
    $query->fields('st',['field_start_date_value']);
    $query->fields('rf',['field_refere_target_id']);
    $query->fields('ed',['field_end_date_value']);
    $query->condition('n.type', 'planned_events');
    $query->condition('rf.field_refere_target_id', $unit_reference);
    $records = $query->execute()->fetchAll();
    $list = [];
    foreach ($records as $key => $value) {
      $list[$key]['id'] = $value->nid;
      $list[$key]['title'] = $value->title;
      $list[$key]['start'] = date('Y-m-d h:m:i', $value->field_start_date_value);
      $list[$key]['end'] = date('Y-m-d h:m:i', $value->field_end_date_value);
      $list[$key]['url'] = '/node/'.$value->nid.'/edit?unit_reference='.$unit_reference;
      $list[$key]['color'] = '#4dabf7';
      $list[$key]['textColor'] = 'black';
    }
    $data  = $list;
    return new JsonResponse($data);
  }

  public function getTitle(Request $request) {
    $results = [];
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));
      $query = \Drupal::database()->select('node_field_data', 'n');
      $query->fields('n', ['title','nid']);
      $query->condition('n.type', 'customers_manual');
      $query->condition('n.title', db_like($typed_string) . '%', 'LIKE');
      $nids = $query->execute()->fetchAll();

      foreach ($nids as $entity) {
        $node_object = Node::load($entity->nid);
        $results[$node_object->get('nid')->value] = $node_object->get('title')->value;
      }
    }
    return new JsonResponse(array_values(array_unique($results)));
  }

}
