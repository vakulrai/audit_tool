<?php

namespace Drupal\aps_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Render\Markup;

/**
 * Class SubscriptionForm.
 */
class SubscriptionForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'subscription_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_date = date('Y-m-d');
    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start Date'),
      '#attributes' => ['id' => 'start-date-license'],
      '#default_value' => $current_date,
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End Date'),
      '#attributes' => ['id' => 'end-date-license'],
      '#default_value' => $current_date,
    ];
    $terms_condition_messsage = Markup::create('<span data-toggle="tooltip" data-placement="top" title="Click here to indicate that you have read and agree to the terms presented in the Terms and Conditions agreement"><p>Standard Terms & Obligations for license agreement</p></span>');
    $form['terms'] = [
      '#type' => 'checkbox',
      '#title' => $terms_condition_messsage,
      '#attributes' => ['id' => 'terms-condition'],
      '#default_value' => 0,
      '#required' => 1,
      '#suffix'=> '<p  id="terms-check" class="form-group error-description"></p>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Renew'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'UpdateSubscription'],
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
  public function UpdateSubscription(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->getValue('terms') == 0) {
      $response->addCommand(new HtmlCommand('#terms-check', t('Please Select terms and Condition.')));
    }
    else{
      $response->addCommand(new RemoveCommand('.ui-dialog'));
    }
    return $response;
  }

}
