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
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

/**
 * Class UpdateFunctionForm.
 */
class UpdateFunctionForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_auditor_function';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) { 
    $form['function_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Function Name'),
      '#required' => TRUE,
      '#suffix'=> '<p  id="function-apply" class="form-group error-description"></p>',
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Message'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'UpdateFunctions'],
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
   * AJAX Callback to update Auditor Score.
   */
  public function UpdateFunctions(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->getValue('function_name') == '') {
      $response->addCommand(new HtmlCommand('#function-apply', t('Please Enter a Valid function name')));
    }
    else{
      $current_user = \Drupal::currentUser();
      $current_user_id = $current_user->id();
      $user = User::load($current_user_id);
      $get_unit_reference = $user->field_reference_id->target_id;
      $get_mr_id = Node::load($get_unit_reference);
      $function_name = $form_state->getValue('function_name');
      $message = $form_state->getValue('description')['value'];
      $notification = '<b>'.$user->name->value.'</b>, Has Applied for <b>'.$function_name.'.</b><br>Message:<br>'.$message;
      notify($get_mr_id->getOwner()->id(), $current_user_id, Markup::create($notification));
      $response->addCommand(new RemoveCommand('.ui-dialog'));
    }
    return $response;
  }

}
