<?php
namespace Drupal\aps_pre_audit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Drupal\aps_pre_audit\Form\GetMoreInfoForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectForm.
 */
class GetTitleList extends ControllerBase {
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

  public function getTitle(Request $request) {
    $results = [];
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));
      $query = \Drupal::database()->select('node_field_data', 'n');
      $query->fields('n', ['title','nid']);
      $query->condition('n.type', 'planned_events');
      $query->condition('n.title', db_like($typed_string) . '%', 'LIKE');
      $nids = $query->execute()->fetchAll();

      foreach ($nids as $entity) {
        $node_object = Node::load($entity->nid);
        $results[$node_object->get('nid')->value] = $node_object->get('title')->value;
      }
    }
    return new JsonResponse(array_values(array_unique($results)));
  }

  public function askForInfo(){
    $response = new AjaxResponse();
    $modal_form = $this->formBuilder->getForm(GetMoreInfoForm::class);
    $response->addCommand(new OpenModalDialogCommand('Please Update The fields.', $modal_form, ['width' => '800']));
    return $response;
  }

   public function getClause(Request $request, $unit_reference='') {
    $results = [];
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));
      if(isset($unit_reference)){
        $node_object = Node::load($unit_reference);
        $get_unit_id = $node_object->get('field_refere')->target_id;
        $query = \Drupal::database()->select('node__field_refere', 'n');
        $query->join('node__field_clause_title', 'c', 'n.entity_id = c.entity_id');
        $query->join('node__field_clause_', 'd', 'c.entity_id = d.entity_id');
        $query->fields('n',['entity_id']);
        $query->fields('c',['field_clause_title_value']);
        $query->fields('d');
        $query->condition('c.bundle', 'clauses');
        $query->condition('n.field_refere_target_id', $get_unit_id);
        $query->condition('c.field_clause_title_value', db_like($typed_string) . '%', 'LIKE');
        $nids = $query->execute()->fetchAll();
        foreach ($nids as $entity) {
          $node_object = Node::load($entity->entity_id);
          $results[$node_object->get('nid')->value] = $node_object->get('field_clause_')->value;
        }
      }
    }
    return new JsonResponse(array_values(array_unique($results)));
  }

}	
?>