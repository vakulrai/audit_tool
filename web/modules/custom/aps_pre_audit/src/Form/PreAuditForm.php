<?php

namespace Drupal\aps_pre_audit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

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
    $details = $this->getAuditDetails();
    $nid = \Drupal::request()->query->get('ref');
    $procedure_no = aps_pre_audit_get_node_value($nid, 'field_procedure_no');
    $procedure_title = aps_pre_audit_get_node_value($procedure_no, 'title');

    $form['procedure_no'] = array(
      '#type' => 'item',
      '#title' => t('Procedure NO: '.$procedure_title),
    );
 
    foreach ($details as $key => $value) {
      $form['audit_qa_'.$key] = array(
        '#type' => 'details', 
        '#title' => 'Q.no.'.$value['sno'], 
        '#attributes' => ['id' => 'tab-'.$key], 
        '#collapsible' => TRUE, 
        '#collapsed' => FALSE,
      ); 
     
      $form['audit_qa_'.$key]['question_desc'][$key] = array(
        '#type' => 'item',
        '#weight' => 0,
        '#markup' => 'Description : '.$value['desc'],
      );

      $form['audit_qa_'.$key]['question_sr_no'][$key] = array(
        '#type' => 'item',
        '#weight' => 5,
        '#markup' => 'S. no : '.$value['sno'],
      );

      $form['audit_qa_'.$key]['question'][$key] = array(
        '#type' => 'item',
        '#weight' => 10,
        '#markup' => 'Question: '.$value['question'],
      );
       
      $answer_options = $this->getAnswers($value['answers']);
      $form['audit_qa_'.$key]['answers'][$key] = array(
       '#type' => 'checkboxes',
       '#title' => 'Choose One',
       '#options' => $answer_options,
       '#weight' => 15,
      );

      $form['audit_qa_'.$key]['finding'][$key] = array(
       '#type' => 'textfield',
       '#title' => 'Findings',
       '#weight' => 20,
      );

      $form['audit_qa_'.$key]['clause_cat'][$key] = array(
       '#type' => 'textfield',
       '#title' => 'Clause Category',
       '#weight' => 20,
      );

      $form['audit_qa_'.$key]['clause_find'][$key] = array(
       '#type' => 'textfield',
       '#title' => 'Clause Finding',
       '#weight' => 20,
      );
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('NEXT'),
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

  static function getAuditDetails() {
    $output = [];
    if($nid = \Drupal::request()->query->get('ref')){
      $query = \Drupal::database()->select('node_revision__field_queries', 'n');
      $query->fields('n',['field_queries_target_id']);
      $query->condition('n.bundle', 'internal_audit');
      $query->condition('n.entity_id', $nid);
      $qa_ref_id = $query->execute()->fetchAll();
      $answers = [];
      foreach ($qa_ref_id as $key_qa => $value_qa) {
        $qa_ref_nid = $value_qa->field_queries_target_id;
        $qa_object = Node::load($qa_ref_nid);
        if($target_id = $qa_object->get('field_qanda')->target_id){
          $paragraph_object = Paragraph::load($target_id);
          $questions_array = $paragraph_object->toArray();
        }
        $output[$key_qa]['sno'] = $paragraph_object->get('field_sub_s_no_')->value;
        $output[$key_qa]['desc'] = $paragraph_object->get('field_description')->value;
        $output[$key_qa]['question'] = $paragraph_object->get('field_question')->value;
        if(count($questions_array['field_answers_n'])){
          foreach ($questions_array['field_answers_n'] as $key => $value) {
             $paragraphs_answer_object = Paragraph::load($value['target_id']);
             $output[$key_qa]['answers'][$value['target_id']] = ['answer' => $paragraphs_answer_object->get('field_answers')->value, 'desc' => $paragraphs_answer_object->get('field_description_answer')->value];
          }
        }
      }
      
    }
    return $output;
  }

  public function getAnswers($data){
    foreach ($data as $key => $value) {
      $answers[] = $value['answer'].' : '. $value['desc'];
    }
    return $answers;
  }

}
