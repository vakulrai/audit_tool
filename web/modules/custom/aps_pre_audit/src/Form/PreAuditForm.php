<?php

namespace Drupal\aps_pre_audit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class PreAuditForm.
 */
class PreAuditForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pre_audit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#cache']['max-age'] = 0;
    $details = $this->getAuditDetails();
    $nid = \Drupal::request()->query->get('ref');
    $current_path = trim(\Drupal::service('path.current')->getPath(), '/');
    $path_args = explode('/', $current_path);
    if(isset($path_args[1])){
      $node_object = Node::load($path_args[1]);
      $event_timestamp = $node_object->get('field_start_date')->value;
    }
    $user_timezone =  drupal_get_user_timezone();
    $event_timestamp = $this->getTimezoneofEventDate($event_timestamp, $user_timezone, $format = 'd/m/Y H:i:s');
    $get_current_timestamp = $this->getCurrentTimestamp($user_timezone);
    $diff = $event_timestamp - $get_current_timestamp;
    $diff = 0;
    if($diff == 0) {
      $disable_fields = 'TRUE';
    }
    else{
      $disable_fields = 'FALSE';
    }
    $procedure_no = aps_pre_audit_get_node_value($nid, 'field_procedure_no');
    $procedure_title = aps_pre_audit_get_node_value($procedure_no, 'title');

    $form['procedure_no'] = array(
      '#type' => 'item',
      '#title' => t('Procedure NO: '.$procedure_title),
    );

    $validators = array(
      'file_validate_extensions' => ['jpg mp4 pdf'],
    );

    foreach ($details as $key => $value) {
      $count_value = count($value['evidence_value']);
      if($count_value == 2){
        $key_1 = [$value['evidence_value'][0]['target_id']];
        $key_2 = [$value['evidence_value'][1]['target_id']];
      }
      elseif ($count_value == 1) {
        $key_1 = [$value['evidence_value'][0]['target_id']];
        $key_2 = '';
      }else{
        $key_1 = '';
        $key_2 = '';
      }

      $form['audit_qa_'.$key] = array(
        '#type' => 'details', 
        '#title' => 'Q.no.'.$value['sno'], 
        '#attributes' => ['id' => 'tab-'.$key], 
        '#collapsible' => TRUE, 
        '#collapsed' => FALSE,
      ); 
     
      $form['audit_qa_'.$key]['question_desc'] = array(
        '#type' => 'item',
        '#weight' => 0,
        '#markup' => 'Description : '.$value['question'],
      );

      $form['audit_qa_'.$key]['question_sr_no'] = array(
        '#type' => 'item',
        '#weight' => 5,
        '#markup' => 'S. no : '.$value['sno'],
      );

      $form['audit_qa_'.$key]['question'.$key] = array(
        '#type' => 'item',
        '#weight' => 10,
        '#title' => 'Question: '.$value['question'],
      );
      
      $answer_options = $this->getAnswers($value['answers'], $value['type']);
      if($value['type'] == 'defined'){
        $form['audit_qa_'.$key]['answers'.$key] = array(
         '#type' => 'radios',
         '#title' => $value['option'],
         '#options' => $answer_options,
         '#weight' => 15,
         '#default_value' => $value['default_checked'],
         '#disabled' => $disable_fields,
        );
      }
      elseif ($value['type'] == 'predefined') {
         $form['audit_qa_'.$key]['answers'.$key] = array(
         '#type' => 'radios',
         '#title' => 'Choose One',
         '#options' => $answer_options,
         '#weight' => 15,
         '#default_value' => $value['default_checked'],
         '#disabled' => $disable_fields,
        );
      }

      $form['audit_qa_'.$key]['finding_img_'.$key] = [
        '#type' => 'managed_file',
        '#name' => 'users_upload',
        '#title' => t('Upload a File'),
        '#size' => 20,
        '#weight' => 20,
        '#description' => t('Upload files'),
        '#upload_validators' => $validators,
        '#upload_location' => 'public://',
        '#default_value' => $key_1,
        '#disabled' => $disable_fields,
      ];

      $form['audit_qa_'.$key]['finding_audio_'.$key] = [
        '#type' => 'managed_file',
        '#name' => 'users_upload',
        '#title' => t('Upload a File'),
        '#size' => 20,
        '#weight' => 20,
        '#description' => t('upload files'),
        '#upload_validators' => $validators,
        '#upload_location' => 'public://',
        '#default_value' => $key_2,
        '#disabled' => $disable_fields,
      ];

      $form['audit_qa_'.$key]['clause_cat'.$key]= array(
       '#type' => 'textfield',
       '#title' => 'Clause Category',
       '#weight' => 20,
       '#disabled' => $disable_fields,
      );

      $form['audit_qa_'.$key]['clause_find'.$key] = array(
       '#type' => 'textfield',
       '#title' => 'Clause Finding',
       '#weight' => 20,
       '#disabled' => $disable_fields,
      );
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'getDetails'],
        'event' => 'click',
      ],
      '#disabled' => $disable_fields,
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

  }

  static function getAuditDetails() {
    $output = [];
    if($nid = \Drupal::request()->query->get('ref')){
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
          // $data = $answer_node_object->toArray();
          $selection = $answer_node_object->get('field_select_query_type')->value;
          if($selection == 'Pdef'){
            $get_question_id = $answer_node_object->get('field_defined_options_default')->getValue();
            foreach ($get_question_id as $k => $val) {
              $ref_id = $val['target_id'];
              $predefined_question_object = Paragraph::load($ref_id);
              $predefined_question_object_array = $predefined_question_object->toArray();
              $output[$ref_id]['default_checked'] = count($predefined_question_object_array['field_checked']) ? $predefined_question_object->get('field_checked')->value : '';
              $output[$ref_id]['type'] = 'predefined';
              $output[$ref_id]['qid'] = $ref_id;
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
              $output[$ref_id]['qid'] = $val['target_id'];
              $output[$ref_id]['sno'] = count($predefined_question_object_array['field_s_no']) ? $predefined_question_object->get('field_s_no')->value : '';
              $output[$ref_id]['question'] = count($predefined_question_object_array['title']) ? $predefined_question_object->get('title')->value : '';
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
        }
      }
    }
    return $output;
  }

  public function getAnswers($data, $type){
    if($type == 'predefined'){
       foreach ($data as $key => $value) {
        $answers[$key] = $key.' : '. $value['answer'];
      }
    }

    if($type == 'defined'){
      $answers['yes'] = 'Yes';
      $answers['no'] = 'No';
      $answers['na'] = 'Yes';
    }
    return $answers;
  }

  public function getAnswersDefaultValue($data){
    $default_value = [];
    foreach ($data as $key => $value) {
      $count = count($data);
      if($count > 1){
        foreach ($data as $i => $j) {
          if($j['checked_value'] == 'Pdef'){
            $default_value = $i;
          }
        }
        
      }
      // else{
      //   if($value['checked_value'] == 'Yes'){
      //     $default_value = $key;
      //   }
      //   else{
      //     $default_value = '';
      //   }
      // }
    }
    return $default_value;
  }

  public function getAnswerId($data){
    foreach ($data as $key => $value) {
      $ids[$value['aid']] = $value['aid'];
    }
    return $ids;
  }
  
  public function getQandAid($type){ 
    $details = $this->getAuditDetails();
    foreach ($details as $key => $value) {
      $id['questions'][] = $value['qid']; 
      $answers = $this->getAnswerId($value['answers']);
      foreach ($answers as $ids => $id_val) {
        $id['answers'][$id_val] = $id_val;
      }
    }
    return $id[$type];
  }

  public function getDetails(array $form, FormStateInterface $form_state){
    $response = new AjaxResponse();
    $details = $this->getAuditDetails();
    foreach ($details as $key => $value) {
      $form_data['answer'][$value['qid']][] = $form_state->getValue('answers'.$key);
      $form_data['question'][$value['qid']][] = $form_state->getValue('finding_img_'.$key)[0];
      $form_data['question'][$value['qid']][] = $form_state->getValue('finding_audio_'.$key)[0];
    }
 
    if(count($form_data['answer'])){
      $id = $this->getQandAid('answers');
      foreach ($form_data['answer'] as $i=>$j) {
        unset($id[$i]);
        if(isset($i)){
          $paragraph_object =  Paragraph::load($i);
          $paragraph_object->set('field_checked', $j[0]);
          $paragraph_object->save();
        }
      }
    }

    if(count($form_data['question'])){
      foreach ($form_data['question'] as $qi => $qj) {
        $id = $this->getQandAid('questions');
        if(count($qj)){
          $paragraph_object =  Paragraph::load($qi);  
          $paragraph_object->set('field_evidence', $qj);
          $paragraph_object->save();
        }
      }
    }

    return $response;
  }

  public function getTimezoneofEventDate($timestamp, $user_timezone, $format = 'd/m/Y H:i:s'){
    $db_timezone = 'UTC';
    $date_object = DateTimePlus::createFromTimestamp($timestamp, $db_timezone);
    $date_object->setTimezone(new \DateTimeZone($user_timezone));
    return $date_object->getTimestamp();
  }

  public function getCurrentTimestamp($user_timezone){
    $db_timezone = 'UTC';
    $get_current_timestamp =  \Drupal::time()->getCurrentTime();
    $current_date_object = DateTimePlus::createFromTimestamp($get_current_timestamp, $db_timezone);
    $current_date_object->setTimezone(new \DateTimeZone($user_timezone));
    return $current_date_object->getTimestamp();
  }

}
