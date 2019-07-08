<?php

namespace Drupal\aps_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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

}
