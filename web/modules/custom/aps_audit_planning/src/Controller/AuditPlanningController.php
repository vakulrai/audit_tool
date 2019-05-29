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
    $query->fields('n',['title']);
    $query->fields('st',['field_start_date_value']);
    $query->fields('ed',['field_end_date_value']);
    $query->condition('n.type', 'planned_events');
    $records = $query->execute()->fetchAll();
    foreach ($records as $key => $value) {
      $list['title'] = $value->title;
      $list['start'] = date('Y-m-d', $value->field_start_date_value);
      $list['end'] = date('Y-m-d', $value->field_end_date_value);
    }
    return new JsonResponse($list);
  }

}
