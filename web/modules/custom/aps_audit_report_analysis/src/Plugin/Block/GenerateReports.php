<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'GenerateReports' block.
 *
 * @Block(
 *  id = "generate_reports",
 *  admin_label = @Translation("Generate reports"),
 * )
 */
class GenerateReports extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $build['#markup'] = '<h1>Generate Reports</h1>';
    $form_class = '\Drupal\aps_audit_report_analysis\Form\GenerateReportsForm';
    $form_builder_class = \Drupal::formBuilder()->getForm($form_class);
    $build['form'] = $form_builder_class;
    return $build;
  }

}
