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
use Drupal\paragraphs\Entity\Paragraph;

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
    if($entity_type != 'user'){
      $bundle = 'node';
    }
    else{
      $bundle = 'user';
    }
    if($entity_type == 'assembly' && strtolower($user_data['type']) == 'assembly'){
      foreach ($user_data as $key => $value) {
        if(strtolower($key) == 'field_shift'){
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
        if(strtolower($key) == 'field_mp_assembly_name'){
          $title = $value;
          $data['title'] = $title;
        }
        if(strtolower($key) == 'field_select_section'){
          $query = \Drupal::database()->select('node_field_data', 'n');
          $query->fields('n',['nid']);
          $query->condition('n.title', $value);
          $query->range(0, 1);
          $nid_by_name = $query->execute()->fetchAll();
          $nid = $nid_by_name[0]->nid;
          $data['field_refere'] = $nid;
        }
        if(strtolower($key) == 'field_refere'){
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
    elseif ($entity_type == 'supplier' && strtolower($user_data['type']) == 'supplier') {
      foreach ($user_data as $key_supplier => $value_supplier) {
        if(strtolower($key_supplier) == 'field_product_list'){
          $get_product_array_supplier = explode(',', $value_supplier);
          unset($user_data['field_product_list']);
          foreach ($get_product_array_supplier as $product) { 
            $user_data['field_product_list'][] = $product;
          }
        }
        if(strtolower($key_supplier) == 'field_refere'){
          $query = \Drupal::database()->select('node_field_data', 'n');
          $query->fields('n',['nid']);
          $query->condition('n.title', $value_supplier);
          $query->range(0, 1);
          $nid_by_name_unit_supplier = $query->execute()->fetchAll();
          unset($user_data['field_refere']);
          $nid_unit_supplier = $nid_by_name_unit_supplier[0]->nid;
          $referenced_node_supplier = [$data['field_refere'], $nid_unit_supplier];
          $user_data['field_refere'] = $referenced_node_supplier;
        }
        if(strtolower($key_supplier) == 'field_supplier_city'){
          $get_city_array_supplier = explode(',', $value_supplier);
          unset($user_data['field_supplier_city']);
            $user_data['field_supplier_city']['country_code'] = $get_city_array_supplier[0];
            $user_data['field_supplier_city']['administrative_area'] = $get_city_array_supplier[1];
            $user_data['field_supplier_city']['locality'] = $get_city_array_supplier[2];
        }    
      }
      $data = $user_data;
    }
    elseif ($entity_type == 'customers_manual' && strtolower($user_data['type']) == 'customers_manual') {
      $data = $user_data;
    }
    elseif ($entity_type == 'user' && strtolower($user_data['type']) == 'user') {
      foreach ($user_data as $user_data_user_key => $user_data_val) {
        if(strtolower($user_data_user_key) == 'field_reference_id'){
            $query = \Drupal::database()->select('node_field_data', 'n');
            $query->fields('n',['nid']);
            $query->condition('n.title', $user_data_val);
            $query->range(0, 1);
            $nid_by_name_unit_supplier = $query->execute()->fetchAll();
            $nid_unit_user = $nid_by_name_unit_supplier[0]->nid;
            $referenced_node_user = ['target_id'=>$nid_unit_user];
            $user_data['field_reference_id'] = $referenced_node_user;
        }
        if(strtolower($user_data_user_key) == 'field_department'){
            $query = \Drupal::database()->select('node_field_data', 'n');
            $query->fields('n',['nid']);
            $query->condition('n.title', $user_data_val);
            $query->range(0, 1);
            $nid_by_name_unit_department = $query->execute()->fetchAll();
            $nid_unit_user_department = $nid_by_name_unit_department[0]->nid;
            $referenced_node_user_department = ['target_id'=>$nid_unit_user_department];
            $user_data['field_department'] = $referenced_node_user_department;
          }
        if(strtolower($user_data_user_key) == 'field_functions_qualified' || strtolower($user_data_user_key) == 'field_score'){
          $function = $user_data['field_functions_qualified'];
          $score = $user_data['field_score'];
          $paragraph = Paragraph::create([
            'field_functions_qualified' => $function,
            'field_score' => $score,
            'type' => 'auditor_functional_details',
          ]);
          $paragraph->save();
          $paragraphp_version = [
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId(),
          ];
          $user_data['field_functions'] = $paragraphp_version;
        }
        if(strtolower($user_data_user_key) == 'field_functions'){
          $user_data['field_phone'] = ['value' => $user_data['field_phone'], 'country' => 'IN'];
          $user_data['roles'] = $roles;
        }
        if(strtolower($user_data_user_key) == 'roles'){
          $roles = explode(',', $user_data['roles']);
          $user_data['roles'] = $roles;
        }
      } 
      $data = $user_data;
    }
    
    //Create Entity form data.
    if($data){
      $data['type'] = $entity_type;
      $save_submission = entity_create($bundle, $data);
      $save_submission->save();
    }
  }

   /*
   * Method to get List of Notifications.
   */
  function notifications() {
      $header = [
        $this->t('Sr. No.'),
        $this->t('Message'),
        $this->t('Timestamp'),
      ];
      $build['tableselect_element'] = [
        '#type' => 'table',
        '#header' => $header,
        '#empty' => t('No content available.'),
      ];
      $data = $this->getNotification();

      if (count($data)) {
        $sr = 1;
        foreach ($data as $key => $value) {
            $build['tableselect_element'][$sr]['sr'] = [
              '#markup' => $sr,
              '#title_display' => 'invisible',
            ];

            $build['tableselect_element'][$sr]['message'] = [
              '#markup' => $value->message ? $value->message : '-',
              '#title_display' => 'invisible',
            ];

            $build['tableselect_element'][$sr]['time'] = [
              '#markup' => $value->timestamp? date('Y-m-d',$value->timestamp): '-',
              '#title_display' => 'invisible',
            ];
            $sr++;
        }
    }
    return $build;
  }

  public function getNotification(){
    $query = \Drupal::database()->select('notifications', 'nf');
    $query->fields('nf',['sr', 'message', 'timestamp']);
    $nids = $query->execute()->fetchAll();
    return $nids;
  }
}	
?>