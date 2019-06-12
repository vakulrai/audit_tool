<?php

namespace Drupal\aps_audit_planning\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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

   public function generateEvents(Request $request) {
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->join('node__field_start_date', 'st', 'n.nid = st.entity_id');
    $query->join('node__field_end_date', 'ed', 'st.entity_id = ed.entity_id');
    $query->fields('n',['title', 'nid']);
    $query->fields('st',['field_start_date_value']);
    $query->fields('ed',['field_end_date_value']);
    $query->condition('n.type', 'planned_events');
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

}
