<?php

namespace Drupal\aps_audit_planning\Controller;

use Drupal\Core\Controller\ControllerBase;

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

}
