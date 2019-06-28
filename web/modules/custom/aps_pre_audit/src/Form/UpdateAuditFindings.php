<?php

namespace Drupal\aps_pre_audit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

/**
 * Class UpdateAuditFindings.
 */
class UpdateAuditFindings extends PreAuditForm {


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
     $reference_id = \Drupal::request()->query->get('audit_reference');
     $details = $this->getAuditDetails($reference_id);
     $validators = array(
        'file_validate_extensions' => ['jpg mp4 pdf'],
     );

     $header = [
        $this->t('Step'),
        $this-> t('Question'),
        $this->t('Evidence'),
        $this-> t('Result'),
        $this-> t('Finding Categories'),
        $this-> t('Clause'),
      ];

      $header_report = [
        $this->t('Occurence'),
        $this-> t('Standard'),
      ];

      $table_options = [$options];
      $form['display'] = array(
        '#type' => 'fieldset', 
        '#title' => t('Pre Audit Report'), 
        '#attributes' => ['id' => 'display'], 
        '#collapsible' => TRUE, 
        '#collapsed' => FALSE,
      );

      $form['display_d_q'] = array(
        '#type' => 'fieldset', 
        '#title' => t('Delta Q'), 
        '#attributes' => ['id' => 'display'], 
        '#collapsible' => TRUE, 
        '#collapsed' => FALSE,
      );

      $form['report_standards'] = array(
        '#type' => 'fieldset',  
        '#attributes' => ['id' => 'report-standards'], 
        '#collapsible' => TRUE, 
        '#collapsed' => FALSE,
        '#weight' => -1,
      );

      $form['report_standards']['header_report'] = array( 
      '#type' => 'table',
      '#header' => $header_report, 
      '#empty' => t('No content available.'), 
      );

      $form['display']['tableselect_element'] = array( 
      '#type' => 'table', 
      '#caption' => $this->t('Checklist'),
      '#header' => $header, 
      '#empty' => t('No content available.'), 
      );

      $form['display_d_q']['tableselect_element_dq'] = array( 
      '#type' => 'table', 
      '#caption' => $this->t('Delta Q'),
      '#header' => $header, 
      '#empty' => t('No content available.'), 
      );
      
      $occurence_options = [
        'first_time' => 'First Time',
        'repetitive' => 'Repetitive',
        'other' => 'Other',
      ];

      $form['report_standards']['header_report'][0]['occurence'] = array(
         '#type' => 'radios',
         '#title' => 'Occurence',
         '#options' => $occurence_options,
         '#default_value' => 'first_time',
      );
      $form['report_standards']['header_report'][0]['other'] = array(
         '#type' => 'textfield',
         '#maxlength' => 5,
         '#size' => 5,
         '#title' => 'Other',
         '#states' => [
            'visible' => [
              'input[name="header_report[0][occurence]"]' => ['value' => 'other']
              ],
          ],
      );

      $standard_options = [
        'iatf' => 'IATF',
        'ehs' => 'EHS',
        'isms' => 'ISMS',
        'lpa' => 'LPA',
      ];

      $form['report_standards']['header_report'][0]['standards'] = array(
         '#type' => 'radios',
         '#title' => 'Standards',
         '#options' => $standard_options,
         '#default_value' =>  'iatf',
      );


      if(count($details)){
        $sr = 1;
        foreach ($details as $key=>$value) {
          if($value['field_answer_type'] == 'non-delta'){
            $form['display']['tableselect_element'][$sr]['srno'] = [
              '#markup' =>$value['sno'] ,
              '#title' => $this->t('Step'),
              '#title_display' => 'invisible',
            ];

            $form['display']['tableselect_element'][$sr]['question'] = [
              '#markup' => $value['question'],
              '#title' => $this->t('Question'),
              '#title_display' => 'invisible',
            ];
            
          if(count($value['evidence_value']) > 1){
            foreach ($value['evidence_value'] as $i => $j) {
              $form['display']['tableselect_element'][$sr][$i]['evidence'] = [
                '#type' => 'managed_file',
                '#name' => 'users_upload',
                '#title' => t('Upload a File'),
                '#size' => 20,
                '#weight' => 20,
                '#description' => t('Upload files'),
                '#upload_validators' => $validators,
                '#upload_location' => 'public://',
                '#default_value' => [$j['target_id']],
              ];
            }
          }
          else{
            $form['display']['tableselect_element'][$sr]['evidence'] = [
            '#type' => 'managed_file',
            '#name' => 'users_upload',
            '#title' => t('Upload a File'),
            '#size' => 20,
            '#weight' => 20,
            '#description' => t('Upload files'),
            '#upload_validators' => $validators,
            '#upload_location' => 'public://',
            '#default_value' => [$value['evidence_value'][0]['target_id']],
            ];
          }

          $form['display']['tableselect_element'][$sr]['result'] = [
            '#markup' => $value['default_checked'],
            '#title' => $this->t('Result'),
            '#title_display' => 'invisible',
          ];

          if($value['type'] == 'predefined'){
            foreach ($value['field_finding_categories'] as $finding => $val) {
              if($term = Term::load($val['target_id'])){
                $name .= $term->getName().'<br>';
              }
              else{
                $name = 'Not Found.';
              }
            }
            $form['display']['tableselect_element'][$sr]['findings_category'] = [
              '#markup' => $name,
              '#title' => $this->t('Finding Categories'),
              '#title_display' => 'invisible',
            ];
          }
          
          if(isset($value['clause_no'])){
            $clause_object = Node::load($value['clause_no']);
            $form['display']['tableselect_element'][$sr]['clause'] = [
              '#markup' => $clause_object->get('title')->value,
              '#title' => $this->t('Finding Categories'),
              '#title_display' => 'invisible',
            ];
          }

          $sr++;

        }
      }
    }

    if(count($details)){
        $srq = 1;
        foreach ($details as $keyq=>$valueq) {
          if($valueq['field_answer_type'] == 'delta'){
            $form['display_d_q']['tableselect_element_dq'][$srq]['srno'] = [
              '#markup' =>$valueq['sno'] ,
              '#title' => $this->t('Step'),
              '#title_display' => 'invisible',
            ];

            $form['display_d_q']['tableselect_element_dq'][$srq]['question'] = [
              '#markup' => $valueq['question'],
              '#title' => $this->t('Question'),
              '#title_display' => 'invisible',
            ];
            
          if(count($valueq['evidence_value']) > 1){
            foreach ($valueq['evidence_value'] as $iq => $jq) {
              $form['display_d_q']['tableselect_element_dq'][$srq][$iq]['evidence'] = [
                '#type' => 'managed_file',
                '#name' => 'users_upload',
                '#title' => t('Upload a File'),
                '#size' => 20,
                '#weight' => 20,
                '#description' => t('Upload files'),
                '#upload_validators' => $validators,
                '#upload_location' => 'public://',
                '#default_value' => [$jq['target_id']],
              ];
            }
          }
          else{
            $form['display_d_q']['tableselect_element_dq'][$srq]['evidence'] = [
            '#type' => 'managed_file',
            '#name' => 'users_upload',
            '#title' => t('Upload a File'),
            '#size' => 20,
            '#weight' => 20,
            '#description' => t('Upload files'),
            '#upload_validators' => $validators,
            '#upload_location' => 'public://',
            '#default_value' => [$valueq['evidence_value'][0]['target_id']],
            ];
          }

          $form['display_d_q']['tableselect_element_dq'][$srq]['result'] = [
            '#markup' => $valueq['default_checked'],
            '#title' => $this->t('Result'),
            '#title_display' => 'invisible',
          ];

          if($valueq['type'] == 'predefined'){
            foreach ($value['field_finding_categories'] as $findingq => $valq) {
              if($termq = Term::load($valq['target_id'])){
                $nameq .= $termq->getName().'<br>';
              }
              else{
                $nameq = 'Not Found.';
              }
            }
            $form['display_d_q']['tableselect_element_dq'][$sr]['findings_category'] = [
              '#markup' => $nameq,
              '#title' => $this->t('Finding Categories'),
              '#title_display' => 'invisible',
            ];
          }

          $srq++;
        }
      }
    }
    
    $form['signoff'] = array(
      '#type' => 'details', 
      '#title' => 'Signatures', 
      '#attributes' => ['id' => 'signoff'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    );
    
    $options_auditee = $this->getUserByRole();
    $form['signoff']['signature_auditee'] = [
      '#type' => 'select',
      '#options' => $options_auditee['auditee'],
      '#title' => t('Finding Categories'),
      '#required' => TRUE,
      '#weight' => 20,
    ];

    $form['signoff']['upload_signature_auditee'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload a File'),
      '#size' => 20,
      '#weight' => 20,
      '#description' => t('Upload files'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
    ];

    $options_auditor = $this->getUserByRole();
    $form['signoff']['signature_auditor'] = [
      '#type' => 'select',
      '#options' => $options_auditor['auditor'],
      '#title' => t('Finding Categories'),
      '#required' => TRUE,
      '#weight' => 20,
    ];

    $form['signoff']['upload_signature_auditor'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload a File'),
      '#size' => 20,
      '#weight' => 20,
      '#description' => t('Upload files'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
    ];

    $options_hod = $this->getUserByRole();
    $form['signoff']['signature_hod'] = [
      '#type' => 'select',
      '#options' => $options_hod['mr_admin'],
      '#title' => t('Finding Categories'),
      '#required' => TRUE,
      '#weight' => 20,
    ];

    $form['signoff']['upload_signature_hod'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload a File'),
      '#size' => 20,
      '#weight' => 20,
      '#description' => t('Upload files'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
    ];

    $options_qms = $this->getUserByRole('qms');
    $form['signoff']['signature_qms'] = [
      '#type' => 'select',
      '#options' => $options_qms,
      '#title' => t('Finding Categories'),
      '#required' => TRUE,
      '#weight' => 20,
    ];

    $form['signoff']['upload_signature_qms'] = [
      '#type' => 'managed_file',
      '#name' => 'users_upload',
      '#title' => t('Upload a File'),
      '#size' => 20,
      '#weight' => 20,
      '#description' => t('Upload files'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
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
        'callback' => [$this, 'submitPreAuditDetails'],
        'event' => 'click',
      ],
      '#disabled' => $disable_fields,
    ];

    return $form;
  }
  
 /**
 * Submit Callback.
 *
 */
  public function submitPreAuditDetails(array $form, FormStateInterface $form_state){
    $reference_id = \Drupal::request()->query->get('event_reference');
    $node_object = Node::load($reference_id);
    $node_object->set('moderation_state', 'post_audit');
    $node_object->save();
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
    // $reference_id = \Drupal::request()->query->get('audit_reference');
    // $details = $this->getAuditDetails($reference_id);
    // echo '<pre>';print_r($details);die;
  }

  public function getUserByRole(){
    $query = \Drupal::database()->select('user__roles', 'u');
    $query->fields('u',['entity_id', 'roles_target_id']);
    $nids = $query->execute()->fetchAll();
    foreach ($nids as $key => $value) {
      $user_object = User::load($value->entity_id);
      if($value->roles_target_id == 'auditor'){
        $user_list['auditor'][$user_object->get('uid')->value] = $user_object->get('name')->value;
      }
      if($value->roles_target_id == 'auditee'){
        $user_list['auditee'][$user_object->get('uid')->value] = $user_object->get('name')->value;
      }
      if($value->roles_target_id == 'mr_admin'){
        $user_list['mr_admin'][$user_object->get('uid')->value] = $user_object->get('name')->value;
      }
    } 
    return $user_list; 
  }

  static function getAuditDetails($reference_id=NULL) {
    $output = [];
    if($reference_id){
      $nid = $reference_id;
    }

    if($nid){
      $query = \Drupal::database()->select('node_revision__field_queries', 'n');
      $query->fields('n',['field_queries_target_id']);
      $query->condition('n.bundle', 'internal_audit');
      $query->condition('n.entity_id', $nid);
      $qa_ref_id = $query->execute()->fetchAll();
      $answers = [];
      $qa_object = Node::load($nid);
      foreach ($qa_ref_id as $key_qa => $value_qa) {
        $qa_ref_nid = $value_qa->field_queries_target_id;
        if($target_id = $qa_object->get('field_queries')->target_id){
          $answer_node_object = Node::load($qa_ref_nid);
          $selection = $answer_node_object->get('field_select_query_type')->value;
          // if($answer_node_object->get('field_select_query_type')->value == 'yes'){
            if($selection == 'Pdef'){
              $get_question_id = $answer_node_object->get('field_defined_options_default')->getValue();
              foreach ($get_question_id as $k => $val) {
                $ref_id = $val['target_id'];
                $predefined_question_object = Paragraph::load($ref_id);
                $predefined_question_object_array = $predefined_question_object->toArray();
                $output[$ref_id]['default_checked'] = count($predefined_question_object_array['field_checked']) ? $predefined_question_object->get('field_checked')->value : '';
                $output[$ref_id]['type'] = 'predefined';
                $output[$ref_id]['field_answer_type'] = $answer_node_object->get('field_answer_type')->value;
                $output[$ref_id]['qid'] = $ref_id;
                $output[$ref_id]['clause_no'] = $predefined_question_object->get('field_clause_no')->target_id;
                $output[$ref_id]['field_finding_categories'] = $predefined_question_object->get('field_finding_categories')->getValue();
                $output[$ref_id]['sno'] = count($predefined_question_object_array['field_sub_s_no_']) ? $predefined_question_object->get('field_sub_s_no_')->value : '';
                $output[$ref_id]['desc'][$predefined_question_object->get('field_answer_optimised')->value] = $predefined_question_object_array['field_answer_optimised'][0]['value'];
                $output[$ref_id]['desc'][$predefined_question_object->get('field_answer_qualified')->value] = $predefined_question_object_array['field_answer_qualified'][0]['value'];
                $output[$ref_id]['desc'][$predefined_question_object->get('field_answers_defined')->value] = $predefined_question_object_array['field_answers_defined'][0]['value'];
                $output[$ref_id]['desc'][$predefined_question_object->get('field_answers_poor')->value] = $predefined_question_object_array['field_answers_poor'][0]['value'];
                $output[$ref_id]['question'] = count($predefined_question_object_array['field_question']) ? $predefined_question_object->get('field_question')->value : '';
                $output[$ref_id]['evidence_value'] = count($predefined_question_object_array['field_evidence']) ? $predefined_question_object->get('field_evidence')->getValue() : '';

                if(count($output[$ref_id]['desc'])){
                  foreach ($output[$ref_id]['desc'] as $key => $value) {
                     $paragraphs_answer_object = Paragraph::load($ref_id);
                     $output[$ref_id]['answers'][$value] = ['aid' => $ref_id,'answer' => $paragraphs_answer_object->get('field_description_'.strtolower($key))->value,'checked_value' => $selection];
                     $output[$ref_id]['question'] = $predefined_question_object->get('field_questions')->value;
                  }
                }
              }
            }
            elseif ($selection == 'Yes') {
              $get_question_id = $answer_node_object->get('field_defined_option_yes_no')->getValue();
              foreach ($get_question_id as $k => $val) {
                 $ref_id = $val['target_id'];
                 $predefined_question_object = Paragraph::load($ref_id);
                $predefined_question_object_array = $predefined_question_object->toArray();
                $output[$ref_id]['default_checked'] = count($predefined_question_object_array['field_checked']) ? $predefined_question_object->get('field_checked')->value : '';
                $output[$ref_id]['type'] = 'defined';
                 $output[$ref_id]['field_answer_type'] = $answer_node_object->get('field_answer_type')->value;
                $output[$ref_id]['qid'] = $val['target_id'];
                $output[$ref_id]['sno'] = count($predefined_question_object_array['field_s_no']) ? $predefined_question_object->get('field_s_no')->value : '';
                $output[$ref_id]['question'] = count($predefined_question_object_array['field_question']) ? $predefined_question_object->get('field_question')->value : '';
                $output[$ref_id]['evidence_value'] = count($predefined_question_object_array['field_evidence']) ? $predefined_question_object->get('field_evidence')->getValue() : '';
                $output[$ref_id]['desc'][$predefined_question_object->get('field_description')->value] = $predefined_question_object->get('field_description')->value;
                $output[$ref_id]['option'] = $predefined_question_object->get('field_description')->value;
                if(count($output[$ref_id]['desc'])){
                  foreach ($output[$ref_id]['desc'] as $key => $value) {
                     $paragraphs_answer_object = Paragraph::load($ref_id);
                     $output[$ref_id]['answers'][$value] = ['aid' => $ref_id,'answer' => $paragraphs_answer_object->get('field_description')->value,'checked_value' => $selection];
                  }
                }
              }
            }
          // }
        }
      }
    }
    return $output;
  }
}
