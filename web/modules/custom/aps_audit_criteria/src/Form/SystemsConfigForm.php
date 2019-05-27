<?php

namespace Drupal\aps_audit_criteria\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SystemsConfigForm.
 */
class SystemsConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'aps_audit_criteria.systemsconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'systems_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $config = $this->config('aps_audit_criteria.systemsconfig');
    $options_document = $this->getVids('system_documents', 27);
    $options_records = $this->getVids('system_documents', 28);
    $options_score = $this->getVids('system_documents', 31);
    // echo '<pre>';print_r($options_score);die;
    $form['documents'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('DOCUMENTS'),
      '#options' => $options_document,
      // '#default_value' => isset($config->get('documents')) ? $config->get('documents') : '',
    ];

    $form['records'] = [
      '#type' => 'checkboxes',
      '#options' => $options_records,
      '#title' => $this->t('RECORDS'),
      // '#default_value' => isset($config->get('records')) ? $config->get('records') : '',
    ];

    $form['score_settings'] = [
      '#type' => 'radios',
      '#options' => $options_score,
      '#title' => $this->t('SCORE SETTINGS'),
      '#description' => $this->t('Define the score level'),
      // '#default_value' => isset($config->get('score_settings')) ? $config->get('score_settings') : '',
    ];

    $form['customer_audit'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('CUSTOMER AUDIT'),
    ];

    $form['customer_audit']['ca_doc'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('DOCUMENTS'),
      '#options' => $options_document,
    ];

    $form['customer_audit']['ca_rec'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('RECORDS'),
      '#options' => $options_document,
    ];
    return parent::buildForm($form, $form_state);
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
    parent::submitForm($form, $form_state);

    $this->config('aps_audit_criteria.systemsconfig')
      ->set('documents', $form_state->getValue('documents'))
      ->set('records', $form_state->getValue('records'))
      ->set('score_settings', $form_state->getValue('score_settings'))
      ->set('customer_audit', $form_state->getValue('customer_audit'))
      ->save();
  }

  // public function getVids($vid, $parent_id){
  //   $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $parent_id);
  //   foreach ($terms as $term) {
  //    $term_data[$term->tid] = $term->name;
  //   }
  //   return $term_data;
  // }

}
