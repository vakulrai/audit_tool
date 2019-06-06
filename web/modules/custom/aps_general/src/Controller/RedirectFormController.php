<?php
namespace Drupal\aps_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Drupal\aps_general\Form\ReportDocument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectForm.
 */
class RedirectFormController extends ControllerBase {
  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The ModalFormExampleController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * Callback for opening the modal form.
   */
  public function openModalForm() {
    $response = new AjaxResponse();
    $modal_form = $this->formBuilder->getForm(ReportDocument::class);
    $response->addCommand(new OpenModalDialogCommand('Please Update The fields.', $modal_form, ['width' => '800']));
    return $response;
  }

  public function redirect_form() {
    return new RedirectResponse('/node/add/clauses');
  }
}	
?>