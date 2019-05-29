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
    if (isset($_REQUEST)) {
    }

    return new JsonResponse($_REQUEST);
  }

}
