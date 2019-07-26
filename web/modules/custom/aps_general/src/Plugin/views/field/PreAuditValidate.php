<?php

namespace Drupal\aps_general\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("remainingday_event_validate")
 */
class PreAuditValidate extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // Return a random text, here you can include your custom logic.
    // Include any namespace required to call the method required to generate
    // the desired output.
    $node = $values->_entity;
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    foreach ($roles as $key => $value) {
      $user_role = $value;
    }
    $select_audit = $node->get('field_select_audit')->target_id;
    $event_start_date_timestamp = $node->get('field_start_date')->value;
    if($node->get('field_audit_type')->value === 'internal'){
      $internal_audit_type = $node->get('field_internal_audit_type')->value;
      switch ($internal_audit_type) {
        case 'systems':
          $audit_reference =  $node->get('field_checklist')->target_id;
          break;

        case 'process':
          $audit_reference =  $node->get('field_checklist')->target_id;
          break;

        case 'product':
          $audit_reference =  $node->get('field_checklist')->target_id;
          break;
        
        default:
          # code...
          break;
      }
    }
    $audit_cycle_settings = getAuditCycleObjectCurrentUnit($node->get('field_refere')->target_id);
    $days_before_event = $audit_cycle_settings->get('field_rescheduling_of_dates_')->value;
    $audit_cycle_time = date('Y-m-d H:i:s',strtotime('-'.$days_before_event.'day', $event_start_date_timestamp));
    $date1 = new \DateTime($audit_cycle_time);
    $date2 = new \DateTime();
    $diff = $date2->diff($date1);
    $months = $diff->m;
    $days = $diff->days; 
    $hours = $diff->h;
    $check_invert_time = $diff->invert;
    $total_hours = $days * 24 + $hours;
    if ($total_hours > 0  && $check_invert_time != 1) {
      if($user_role == 'auditor'){
        $form['add_delta_qa'] = [
          '#type' => 'link',
          '#title' => t('Pre Audit'),
          '#url' => Url::fromUserInput('/preaudit/'.$node->id().'?ref='.$audit_reference),
        ];
      }
      elseif ($user_role == 'auditee') {
        $form['add_delta_qa'] = [
          '#type' => 'link',
          '#title' => t('UPLOAD'),
          '#url' => Url::fromUserInput('/documentrecords/'.$node->id()),
        ];
      }
    }
    else{
      $form['add_delta_qa'] = [
          '#markup' => 'Last Date of Pre-audit was :<b>'.$audit_cycle_time.'</b>',
        ];
    }
    return $form;
  }

}
