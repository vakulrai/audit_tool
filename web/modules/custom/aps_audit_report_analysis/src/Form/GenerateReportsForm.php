<?php

namespace Drupal\aps_audit_report_analysis\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\aps_audit_report_analysis\Controller\GenerateReports;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Class GenerateReports.
 */
class GenerateReportsForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'generate_reports';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
    if(count($entity_audit_cycle)){
      $cycle_type = $entity_audit_cycle->get('field_cycle_type')->value;
      if($cycle_type == 0){
        $audit_cycle_start_date = $entity_audit_cycle->get('field_financial_dates')->value;
        $audit_cycle_end_date = $entity_audit_cycle->get('field_financial_dates')->end_value;
      }
      elseif ($cycle_type == 1) {
        $audit_cycle_start_date = $entity_audit_cycle->get('field_calendar_date')->value;
        $audit_cycle_end_date = $entity_audit_cycle->get('field_calendar_date')->end_value;
      }
    }

    $options = [
      'activity_related' => 'Activity Related',
      'audit_cycle' => 'Audit Cycle',
      'risk' => 'Risk',
    ];

    $form['audit_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $options,
      '#attributes' => ['id' => 'audit-type-report'],
      '#default_value' => 'all',
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start Date'),
      '#attributes' => ['id' => 'start-date-report'],
      '#default_value' => $audit_cycle_start_date,
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End Date'),
      '#attributes' => ['id' => 'end-date-report'],
      '#default_value' => $audit_cycle_end_date,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate'),
      '#attributes' => [
        'class' => [
          'use-ajax',
          'btn-colored',
          'btn',
          'btn-raised',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'GenerateReport'],
        'event' => 'click',
      ],
      '#prefix' =>'<p id="display-status-reports">',
      '#suffix' =>'</p>',
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

   /**
   * AJAX Callback to generate report.
   */
  public function GenerateReport(array $form, FormStateInterface $form_state) {
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $response = new AjaxResponse();
    global $base_url;
    $form_values = $form_state->getValues();
    $response->addCommand(new RedirectCommand('/generate_reports/'.$form_values['audit_type'].'/'.$uri[1]));
    return $response;
  }

}
