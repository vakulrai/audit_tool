<?php
namespace Drupal\aps_general\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Drupal\aps_general\Form\ReportDocument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\RedirectCommand;

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
    $response = new AjaxResponse();
    $id = \Drupal::request()->query->get('id');
    $config = \Drupal::service('config.factory')->getEditable('aps_general.adduserroles');
    $config_info = \Drupal::service('config.factory')->getEditable('aps_pre_audit.getmoreinfo');
    $config_info->set('nid', $id)->save();
    $config->set('doc_id', $id);
    $config->save();
    if($config->get('doc_id') == $id){
      $response->addCommand(new RedirectCommand('/documentinternalrecords/'.$id));
    }  
    return $response;
  }

  /*
   * Batch Callback for CSV upload.
   */
  public function createEntity($user_data, $entity_type, &$context){
    $message = 'Csv Imported';
    $results = array();
    RedirectFormController::create_entity($user_data, $entity_type);
    $context['message'] = $message;
    $context['results']['count'][] = $user_data;
    $context['results']['type'] = $entity_type;
  }
  
  /*
   * Batch Finished Callback for CSV upload.
   */
  public function createEntityFinishedCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results['count']),
        'One post processed.', '@count Content has been added of type <b>'.$results['type'].'</b>.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }

  /*
   * Method to create entity from CSV.
   */
  function create_entity($user_data, $entity_type) {
    if($entity_type == 'assembly' && strtolower($user_data['type']) == 'assembly'){
      foreach ($user_data as $key => $value) {
        if(strtolower($key) == 'shift'){
          $get_shift_array = explode(',', $value);
          foreach ($get_shift_array as $shift => $shift_value) {
            $properties['name'] = $shift_value;
            $properties['vid'] = 'shift';
            $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
            $term = reset($terms);
            $id = !empty($term) ? $term->id() : 0;
            $shift_terms[] = $id;
            $data['field_shift'] = $shift_terms;
          }
        }
        if(strtolower($key) == 'title'){
          $title = $value;
          $data['title'] = $title;
        }
        if(strtolower($key) == 'section'){
          $query = \Drupal::database()->select('node_field_data', 'n');
          $query->fields('n',['nid']);
          $query->condition('n.title', $value);
          $query->range(0, 1);
          $nid_by_name = $query->execute()->fetchAll();
          $nid = $nid_by_name[0]->nid;
          $data['field_refere'] = $nid;
        }
        if(strtolower($key) == 'unit'){
          $query = \Drupal::database()->select('node_field_data', 'n');
          $query->fields('n',['nid']);
          $query->condition('n.title', $value);
          $query->range(0, 1);
          $nid_by_name_unit = $query->execute()->fetchAll();
          $nid_unit = $nid_by_name_unit[0]->nid;
          $referenced_node = [$data['field_refere'], $nid_unit];
          $data['field_refere'] = $referenced_node;
        }
      }
    }
    elseif ($entity_type == 'manufacturing_process' && strtolower($user_data['type']) == 'manufacturing_process') {
      foreach ($user_data as $key => $value) {
        if(strtolower($key) == 'title'){
          $title = $value;
          $data['title'] = $title;
        }
        if(strtolower($key) == 's_no'){
          $s_no = $value;
          $data['field_sr_no'] = $s_no;
        }
        if(strtolower($key) == 'section'){
          $query = \Drupal::database()->select('node_field_data', 'n');
          $query->fields('n',['nid']);
          $query->condition('n.title', $value);
          $query->range(0, 1);
          $nid_by_name = $query->execute()->fetchAll();
          $nid = $nid_by_name[0]->nid;
          $data['field_refere'] = $nid;
        }
        if(strtolower($key) == 'unit'){
          $query = \Drupal::database()->select('node_field_data', 'n');
          $query->fields('n',['nid']);
          $query->condition('n.title', $value);
          $query->range(0, 1);
          $nid_by_name_unit = $query->execute()->fetchAll();
          $nid_unit = $nid_by_name_unit[0]->nid;
          $referenced_node = [$data['field_refere'], $nid_unit];
          $data['field_refere'] = $referenced_node;
        }
      }
    }
    //Create Entity form data.
    if($data){
      $data['type'] = $entity_type;
      $save_submission = entity_create('node', $data);
      $save_submission->save();
    }
  }
}	
?>