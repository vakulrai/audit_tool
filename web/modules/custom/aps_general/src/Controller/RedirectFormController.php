<?php
namespace Drupal\aps_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectForm.
 */
class RedirectFormController extends ControllerBase {


  public function redirect_form() {
    return new RedirectResponse('/node/add/clauses');
  }
}	
?>