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
           $respose['response'] = TRUE;
        } 
        catch(\Exception $e) {
          $respose['response'] = FALSE;
        }
      }
    }
    return new JsonResponse( $response );
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
      $list[$key]['url'] = '/node/'.$value->nid.'/edit';
      $list[$key]['color'] = 'pink';
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
