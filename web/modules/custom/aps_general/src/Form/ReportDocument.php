<?php

namespace Drupal\aps_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;


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

    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    foreach ($roles as $key => $value) {
      $user_role = $value;
    }

    $term_data = [];
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('reasons');
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
   
    if($user_role === 'auditor'){
      $option_to_proceed = ['execute' => 'Execute', 'report' => 'Report'];
      $form['customer_audit']['option_proceed'] = [
       '#type' => 'radios',
       '#title' => 'Select One',
       '#options' => $option_to_proceed,
       '#weight' => 0,
       '#suffix'=> '<p id="execute-audit"></p>',
      ];

      $form['customer_audit']['reasons'] = [
        '#type' => 'select',
        '#title' => $this->t('Reasons'),
        '#options' => $term_data,
        '#states' => [
          'visible' => [
            ':input[name="option_proceed"]' => ['value' => 'report'],
          ],
        ],
        '#weight' => 10,
      ];

      $form['customer_audit']['reasons_other'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Others'),
         '#states' => [
          'visible' => [
            ':input[name="reasons"]' => ['value' => '250'],
          ],
        ],
        '#weight' => 15,
      ];
    }
    elseif($user_role == 'auditee'){
      $form['customer_audit']['reasons'] = [
        '#type' => 'select',
        '#title' => $this->t('Reasons'),
        '#options' => $term_data,
      ];

      $form['customer_audit']['reasons_other'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Others'),
      ];
    }
    
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
    // // Display result.
    // foreach ($form_state->getValues() as $key => $value) {
    //   drupal_set_message($key . ': ' . $value);
    // }

  }

   /**
   * AJAX Callback to update report.
   */
  public function UpdateReport(array $form, FormStateInterface $form_state) {
    global $base_url;
    $response = new AjaxResponse();
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    $form_values = $form_state->getValues();

    foreach ($roles as $key => $value) {
      $user_role = $value;
    }
    
    if($nid = \Drupal::request()->query->get('id')){
      $node_object = Node::load($nid);
      if($user_role == 'auditor' && $form_values['option_proceed'] == 'execute'){
        // $node_object->set('field_proceed_with_audit', 'yes');
        if($node_object->get('field_proceed_with_audit')->value == 'no'){
          $response->addCommand(new HtmlCommand('#execute-audit', t('Audit has been Reported. Can\'t Proceed.')));
        }
        else{
          $response->addCommand(new RedirectCommand('/preaudit/'.$node_object->id().'?ref='.$node_object->field_checklist->target_id));
        }
      }
      elseif ($user_role == 'auditee' || $user_role == 'auditor') {
        $reason = Paragraph::load($node_object->field_audit_reasons->target_id);
        $term = Term::load($reason->field_reason->target_id);
        $term_name = $term->getName();
        $paragraph = Paragraph::create([
        'field_reason' => $form_values['reasons'],
        'field_others' => $form_values['reasons_other'],
        'type' => 'report_reason',
        ]);
        $paragraph->save();
        $paragraph_version = [
          [
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId(),
          ],
        ];
        if($node_object->bundle() == 'pre_audit_records'){
          $node_object->set('field_reasons_data', $paragraph_version);
        }
        elseif ($node_object->bundle() == 'planned_events') {
          $node_object->set('field_audit_reasons', $paragraph_version);
          $node_object->set('field_proceed_with_audit', 'no');
        }
        $link = $base_url.Url::fromRoute('entity.node.edit_form',['node' => $nid])->toString();
        $action_link = '<a href="'.$link.'">Link</a>';
        $message = 'The Audit has been Reported with category: <b>'.$term_name.'<br>Please check the Action link.'.$action_link;
        notify($nid, $message);
        $response->addCommand(new OpenModalDialogCommand("Success!", 'Report Submitted Successfully.', ['width' => 800]));
      }
    }
    $node_object->save();
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
