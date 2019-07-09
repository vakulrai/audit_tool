<?php

namespace Drupal\aps_audit_report_analysis\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PlanningControlForm.
 */
class PlanningControlForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'planning_control_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $entityManager = \Drupal::service('entity_field.manager');
    $fields = $entityManager->getFieldStorageDefinitions('node');
    $options = options_allowed_values($fields['field_audit_type']);
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);

    $options['all'] = 'ALL';
    //GEtting Audit cycle values from "audit_cycle" configurations.
    $query = \Drupal::database()->select('audit_cycle__field_unit_reference', 'h');
    $query->fields('h',['entity_id']);
    $query->condition('h.field_unit_reference_target_id', $uri[1]);
    $query->range(0, 1);
    $nids = $query->execute()->fetchAll();
    $node_storage = \Drupal::entityManager()->getStorage('audit_cycle');
    $entity_audit_cycle = $node_storage->load($nids[0]->entity_id);
    $cycle_type = $entity_audit_cycle->get('field_cycle_type')->value;
    if($cycle_type == 0){
      $audit_cycle_start_date = $entity_audit_cycle->get('field_financial_dates')->value;
      $audit_cycle_end_date = $entity_audit_cycle->get('field_financial_dates')->end_value;
    }
    elseif ($cycle_type == 1) {
      $audit_cycle_start_date = $entity_audit_cycle->get('field_calendar_date')->value;
      $audit_cycle_end_date = $entity_audit_cycle->get('field_calendar_date')->end_value;
    }

    $form['audit_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Audit Type'),
      '#options' => $options,
      '#attributes' => ['id' => 'audit-type'],
      '#default_value' => 'all',
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start Date'),
      '#attributes' => ['id' => 'start-date'],
      '#default_value' => $audit_cycle_start_date,
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End Date'),
      '#attributes' => ['id' => 'end-date'],
      '#default_value' => $audit_cycle_end_date,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply'),
      '#attributes' => ['id' => 'planning-submit'],
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
