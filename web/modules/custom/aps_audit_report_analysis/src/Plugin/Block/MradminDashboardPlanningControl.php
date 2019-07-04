<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\aps_audit_report_analysis\Controller\GetAuditCoverageDetails;
use Symfony\Component\HttpFoundation\Request;
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
    $build = [];
    $request = new Request;
    $get_audit_total = GetAuditCoverageDetails::getAuditDetails($request);
    $response = $get_audit_total->getContent();
    $form_class = '\Drupal\aps_audit_report_analysis\Form\PlanningControlForm';
    $form_builder_class = \Drupal::formBuilder()->getForm($form_class);
    $response = json_decode($response);
    $total = 0;
    foreach ($response as $key => $value) {
      $total += $value->y;
    }
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
    return $build;
  }

}
