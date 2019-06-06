<?php

namespace Drupal\aps_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ReportDocument.
 */
class ReportDocument extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'report_document';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $nid = \Drupal::request()->query->get('id');
  	$form['customer_audit'] = [
      '#type' => 'fieldset',
      '#title' => 'Report',
    ];
    
    $term_data = [];
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('reasons');
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
   
    $form['customer_audit']['reasons'] = [
      '#type' => 'select',
      '#title' => $this->t('Reasons'),
      '#options' => $term_data,
    ];

    $form['customer_audit']['reasons_other'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Others'),
    ];
    
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'UpdateReport'],
        'event' => 'click',
      ],
    ];
    
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
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

   /**
   * AJAX Callback to update report.
   */
  public function UpdateReport(array $form, FormStateInterface $form_state) {

  }

}
