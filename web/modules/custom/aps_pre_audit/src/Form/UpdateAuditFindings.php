<?php

namespace Drupal\aps_pre_audit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
/**
 * Class UpdateAuditFindings.
 */
class UpdateAuditFindings extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_audit_findings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $validators = array(
      'file_validate_extensions' => ['jpg png pdf'],
    );

    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Please fill in the Detials'),
    ];
    
    $form['field_significant_findings'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Significant Findings'),
    ];
    // Gather the number of names in the form already.
    $num_names = $form_state->get('num_names');
    // We have to ensure that there is at least one name field.
    if ($num_names === NULL) {
      $name_field = $form_state->set('num_names', 1);
      $num_names = 1;
    }

    $form['#tree'] = TRUE;
    $form['names_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Root Cause Analysis'),
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $num_names; $i++) {
      $form['names_fieldset']['name'][$i] = [
        '#type' => 'textfield',
      ];
    }

    $form['names_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['names_fieldset']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];
    // If there is more than one name, add the remove button.
    if ($num_names > 1) {
      $form['names_fieldset']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'names-fieldset-wrapper',
        ],
      ];
    }


    $form['field_further_actions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Further Actions'),
    ];

    $form['field_summary'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Summary'),
    ];
    
    $form['signatures'] = [
      '#type' => 'details',
      '#title' => $this->t('Upload Signatures'),
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $form['signatures']['auditee'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload Auditee Signatures'),
      '#size' => 20,
      '#weight' => 20,
      '#description' => t('Upload files'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
    ];

    $form['signatures']['auditor'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload auditor Signatures'),
      '#size' => 20,
      '#weight' => 20,
      '#description' => t('Upload files'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
    ];

    $form['signatures']['hod'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload HOD Signatures'),
      '#size' => 20,
      '#weight' => 20,
      '#description' => t('Upload files'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
    ];

    $form['signatures']['qms'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload QMS Signatures'),
      '#size' => 20,
      '#weight' => 20,
      '#description' => t('Upload files'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['names_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    $add_button = $name_field + 1;
    $form_state->set('num_names', $add_button);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_names', $remove_button);
    }
    $form_state->setRebuild();
  }

  /**
   * Final submit handler.
   *
   * Reports what values were finally set.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues([ 'name', 'field_significant_findings', 'field_further_actions', 'field_summary']); 
    $data['field_significant_findings'] = $values['field_significant_findings'];
    $data['field_further_actions'] = $values['field_significant_findings'];
    $data['field_summary'] = $values['field_significant_findings'];
    $data['field_root_cause_analysis'] = $values['names_fieldset']['name'];

    $data['field_upload_auditee_signatures'] = $values['signatures']['auditee'];
    $data['field_upload_auditor_signatures'] = $values['signatures']['auditor'];
    $data['field_upload_hod_signatures'] = $values['signatures']['hod'];
    $data['field_upload_qms_signatures'] = $values['signatures']['qms'];
    if($nid = \Drupal::request()->query->get('audit_reference')){
      $node_object = Node::load($nid);
      foreach ($data as $key => $value) {
        if($value){
          $node_object->set($key, $value);
        }
      }
      $node_object->save();
    }
    $output = $this->t('Details has Been Submitted Successfuly to: @names', [
      '@names' => implode(', ', $node_object->get('title')->value),
    ]
    );
    $this->messenger()->addMessage($output);
  }

}
