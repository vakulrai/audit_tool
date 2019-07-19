<?php

namespace Drupal\aps_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Class CsvEntityImport.
 */
class CsvEntityImport extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csv_entity_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#cache']['max-age'] = 0;
    $validators = [
      'file_validate_extensions' => ['csv'],
    ];

    $options = [
      'assembly' => 'Assembly',
      'manufacturing_process' => 'Manufacturing Process',
      'customers_manual' => 'Customer Manual',
      'supplier' => 'Supplier',
      'user' => 'User',
    ];

    $form['data_import'] = [
      '#type' => 'fieldset', 
      '#title' => t('Import Data'), 
      '#attributes' => ['id' => 'data-import'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $form['data_import']['import_type'] = [
      '#type' => 'select',
      '#options' => $options,
      '#required' => TRUE,
      '#title' => t('List of '. $type),
      '#required' => TRUE,
      '#ajax' => [
          'callback' => '::EntityGenerateFields',
          'wrapper' => 'field-list',
          'event' => 'change',
          'effect' => 'fade',
        ],
    ];

    $form['data_import']['field_list'] = [
      '#type' => 'container',
      '#markup' => '',
      '#attributes' => ['id' => 'field-list'],
      '#prefix' => '<div id="field-info"',
      '#suffix' => '</div>',
    ];
    
    $form['data_import']['files'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload a File'),
      '#size' => 20,
      '#weight' => 200,
      '#description' => t('Select the CSV file to be imported'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
      // '#required' => TRUE,
      '#tree' => TRUE,
    ];

    $form['data_import']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#weight' => 200,
      '#value' => t('Import CSV'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    // echo '<pre>';print_r($form_values);die;
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_values['files'][0]);
    $entity_name = $form_values['import_type'];
    $file_name = $file->get('filename')->value;
    $file_uri = $file->get('uri')->value;
    if($file_uri) {
      $data = $this->csvtoarray($file_uri, ',');
      foreach($data as $row) {
        $operations[] = ['\Drupal\aps_general\Controller\RedirectFormController::createEntity', [$row, $entity_name]];
      }
      $batch = array(
        'title' => t('Creating '.$entity_name.' Entity...'),
        'operations' => $operations,
        'init_message' => t('Import started.'),
        'finished' => '\Drupal\aps_general\Controller\RedirectFormController::createEntityFinishedCallback',
      );
      batch_set($batch);
    }
    else {

    } 
  }

  public function csvtoarray($filename='', $delimiter) {
    if(!file_exists($filename) || !is_readable($filename)) return FALSE;
    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE ) {
      while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
      {
        if(!$header){
          $header = $row;
        }else{
          $data[] = array_combine($header, $row);
        }
      }
      fclose($handle);
    }
    return $data;
  }

   /**
   * AJAX Callback to get avilable fields.
   */
  public function EntityGenerateFields(array &$form, FormStateInterface $form_state) {
    $entity_type_id = 'user';
    $bundle = $form_state->getValue('import_type');
    if($bundle != 'user'){
      $entity_type_id = 'node';
    }
    else{
      $entity_type_id = 'user';
    }
    $fields_markup = '<b>Please Create a CSV using the Available Fields. Separate Multivalue with ","</b> </br><b>[</b>';
    $field_definition_object = \Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle);
    if($bundle == 'user'){
      unset($field_definition_object['uid']);
      unset($field_definition_object['uuid']);
      unset($field_definition_object['langcode']);
      unset($field_definition_object['preferred_langcode']);
      unset($field_definition_object['timezone']);
      unset($field_definition_object['created']);
      unset($field_definition_object['changed']);
      unset($field_definition_object['access']);
      unset($field_definition_object['login']);
      unset($field_definition_object['init']);
      unset($field_definition_object['default_langcode']);
      unset($field_definition_object['preferred_admin_langcode']);
    }else{
      unset($field_definition_object['status']);
      unset($field_definition_object['promote']);
      unset($field_definition_object['moderation_state']);
      unset($field_definition_object['body']);
    }
    foreach ($field_definition_object as $field_name => $field_definition) {
      if($field_name != 'status' || $field_name != 'promote' || $field_name != 'moderation_state' || $field_name != 'body'){
        if($bundle != 'user'){
          if (!empty($field_definition->getTargetBundle())) {
            if($field_name == 'field_refere'){
              $fields_markup .= '<b>(Name Of Unit) </b>';
            }
            $fields_markup .= $field_name . ', ';
          }
        }
        else{
          $fields_markup .= $field_name . ', ';
        }
      }
    }
    $fields_markup .= '<b>]</b>';
    $avilable_fields = $fields_markup;
    $ajax_response = new AjaxResponse();
    $renderer = \Drupal::service('renderer');
    $elem = [
      '#type' => 'container',
      '#markup' => $avilable_fields,
      '#attributes' => ['id' => 'field-list'],
      '#prefix' => '<div id="field-info"',
      '#suffix' => '</div>',
    ];
    $ajax_response->addCommand(new ReplaceCommand('#field-info', $renderer->render($elem)));
    return $ajax_response;
  }

}
