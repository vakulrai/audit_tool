<?php

namespace Drupal\aps_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AddUserRolesForm.
 */
class AddUserRolesForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_user_roles_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['add_roles'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Add Roles'),
    ];

    $form['add_roles'] ['select_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add'),
      '#default_value' => $config->get('select_title'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
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
