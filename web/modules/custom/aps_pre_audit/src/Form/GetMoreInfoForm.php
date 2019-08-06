<?php

namespace Drupal\aps_pre_audit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class GetMoreInfoForm.
 */
class GetMoreInfoForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'get_more_info_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['get_info'] = [
      '#type' => 'fieldset',
      '#title' => 'Report',
    ];

    $form['get_info']['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter your query..'),
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
        'callback' => [$this, 'cancelInfo'],
        'event' => 'click',
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Request Info'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'requestInfo'],
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
    
  }

   /**
   * AJAX Callback to update report.
   */
  public function requestInfo(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $user_timezone =  drupal_get_user_timezone();
    $config = \Drupal::service('config.factory')->getEditable('aps_pre_audit.getmoreinfo');
    if($nid = $config->get('nid')){
      $uid = \Drupal::currentUser()->id();
      $get_current_timestamp = getCurrentTimestamp($user_timezone);
      $notifictaion_insert = \Drupal::database()->insert('notifications');
      $message = $form_state->getValues('message')['message'];
      $notifictaion_insert->fields([
        'nid' => $nid,
        'uid' => $uid,
        'message' => $message,
        'timestamp' => $get_current_timestamp,
        'status' => 0,
      ]);
      $notifictaion_insert->execute();
    }
    $response->addCommand(new OpenModalDialogCommand("Success!", 'Notification sent to MR', ['width' => 800]));
    return $response;
  }

  /**
   * AJAX Callback to update report.
   */
  public function cancelInfo(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new RemoveCommand('.ui-dialog'));
    return $response;
  }

}
