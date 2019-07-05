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
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('entity_upload')[0]);
    // $form_values = $form_state->getValues();
    // $entity_name = 'assembly';
    // $file_name = $file->get('filename')->value;
    // $file_uri = $file->get('uri')->value;
print_r($form_state->get('entity_upload')->value);die;
    if($file_uri) {
      $data = $this->csvtoarray($file_uri, ',');
      foreach($data as $row) {
        $operations[] = ['\Drupal\aps_general\RedirectFormController::createEntity', [$row, $entity_name]];
      }
      $batch = array(
        'title' => t('Creating '.$entity_name.' Entity...'),
        'operations' => $operations,
        'init_message' => t('Import started.'),
        'finished' => '\Drupal\aps_general\RedirectFormController::createEntityFinishedCallback',
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
