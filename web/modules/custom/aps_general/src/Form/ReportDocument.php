<?php

namespace Drupal\aps_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RemoveCommand;

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
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'CancelUpdateReport'],
        'event' => 'click',
      ],
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
    $response = new AjaxResponse();
    if($nid = \Drupal::request()->query->get('id')){
      $form_values = $form_state->getValues();
      $node_object = Node::load($nid);
      $paragraph = Paragraph::create([
        'field_reason' => $form_values['reasons'],
        'field_others' => $form_values['reasons_other'],
        'type' => 'report_reason',
      ]);
      $paragraph->save();

      $paragraphp_version = [
        [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ],
      ];

      $node_object->set('field_reasons_data', $paragraphp_version);
      $node_object->save();
    }
    $response->addCommand(new OpenModalDialogCommand("Success!", 'Report Submitted Successfully.', ['width' => 800]));
    return $response;
  }

   /**
   * AJAX Callback to update report.
   */
  public function CancelUpdateReport(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new RemoveCommand('.ui-dialog'));
    return $response;
  }


}
