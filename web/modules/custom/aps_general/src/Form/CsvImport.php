<?php

namespace Drupal\aps_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CsvImport.
 */
class CsvImport extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'csv_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $validators = array(
      'file_validate_extensions' => array('csv'),
    );
    
    $form['entity_upload'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload a File'),
      '#size' => 20,
      '#weight' => 200,
      '#description' => t('Select the CSV file to be imported'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
      '#required' => TRUE,
    ];

    $form['submit'] = [
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
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
