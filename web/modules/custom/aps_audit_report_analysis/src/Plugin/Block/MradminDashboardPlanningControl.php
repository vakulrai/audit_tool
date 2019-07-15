<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\aps_audit_report_analysis\Controller\GetAuditCoverageDetails;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Provides a 'MradminDashboardPlanningControl' block.
 *
 * @Block(
 *  id = "mradmin_dashboard_planning_control",
 *  admin_label = @Translation("Mradmin dashboard planning control"),
 * )
 */
class MradminDashboardPlanningControl extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $build['#cache']['max-age'] = 0;
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $build = [];
    $request = new Request;
    $get_audit_total = GetAuditCoverageDetails::getAuditDetails();
    $response = $get_audit_total->getContent();
    $form_class = '\Drupal\aps_audit_report_analysis\Form\PlanningControlForm';
    $form_builder_class = \Drupal::formBuilder()->getForm($form_class);
    $response = json_decode($response);
    $total = 0;
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->join('node__field_refere', 'rf', 'n.nid = rf.entity_id');
    $query->join('content_moderation_state_field_data', 'cm', 'rf.entity_id = cm.content_entity_id');
    $query->fields('n',['nid', 'title', 'created']);
    $query->fields('rf',['field_refere_target_id']);
    $query->fields('cm',['revision_id', 'moderation_state']);
    $query->condition('n.type', 'planned_events');
    $query->condition('rf.field_refere_target_id', $uri[1]);
    $data = $query->execute()->fetchAll();
    $total = count($data);
    $build['mradmin_dashboard_planning_control']['#markup'] = '<h1>Planning Control</h1>';
    $build['mradmin_dashboard_planning_control']['fieldset'] = [
      '#type' => 'fieldset',
      // '#title' => $this->t('Planning Control'),
    ];
    $build['mradmin_dashboard_planning_control']['fieldset']['total'] = [
      '#markup' => 'Total no of Audits: '.$total,
    ];

    $build['mradmin_dashboard_planning_control']['fieldset']['form'] = $form_builder_class;
    $build['mradmin_dashboard_planning_control']['fieldset']['container_element']['#markup'] = '<div id="container" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>';
    $build['mradmin_dashboard_planning_control']['#attached']['library'][] = 'aps_audit_report_analysis/aps_dashboard_audit_planning_js';
    $build['#attached']['drupalSettings']['siteBaseUrl'] = $base_url;
    $build['#attached']['drupalSettings']['auditDetail'] = $response;
    $build['#attached']['drupalSettings']['unitReference'] = $uri[1];
    return $build;
  }

}
