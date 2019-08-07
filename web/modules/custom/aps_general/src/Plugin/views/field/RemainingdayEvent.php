<?php

namespace Drupal\aps_general\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Component\Datetime\DateTimePlus;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("remainingday_event")
 */
class RemainingdayEvent extends FieldPluginBase {

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
    $event_start_date_timestamp = $node->get('field_start_date')->value;
    $audit_cycle_settings = getAuditCycleObjectCurrentUnit($node->get('field_refere')->target_id);
    if(isset($audit_cycle_settings->get('field_car_release_by_auidtee_')->value)){
      $days_before_event = $audit_cycle_settings->get('field_rescheduling_of_dates_')->value;
    }
    else{
      $days_before_event = 0;
    }
    $audit_cycle_time = date('Y-m-d H:i:s',strtotime('-'.$days_before_event.'day', $event_start_date_timestamp));
    $date1 = new \DateTime($audit_cycle_time);
    $date2 = new \DateTime();
    $diff = $date2->diff($date1);
    $months = $diff->m;
    $days = $diff->days; 
    $hours = $diff->h;
    $check_invert_time = $diff->invert;
    $total_hours = $days * 24 + $hours;
    $minutes = $diff->i;

    if($node->get('moderation_state')->value == 'release_audit' || $node->get('moderation_state')->value == 'post_audit'){
      if ($total_hours >= 0 && $check_invert_time != 1) {
        $time_remaining = $days .' Days '. $hours . ' Hour '.$minutes. ' Minutes';
        $form['days'] = [
          '#markup' => $time_remaining,
          '#suffix' => '<br><p class="event-date"><b>Note:<br>Last day to Reschedule Event is: '. $audit_cycle_time.'</b></p>',
        ];
        $node->set('field_pre_audit_status', 'intime');
      }
      else{
        $time_remaining = 'Reschedule Date Has been Passed,<br>Last date Was: <b>'.$audit_cycle_time.'</b>';
        $form['days'] = [
          '#markup' => $time_remaining,
        ];
        $node->set('field_pre_audit_status', 'notintime');
      }
    }
    elseif($node->get('moderation_state')->value == 'submit_audit'){
      $time_remaining = 'Audit Has been Submitted.';
      $form['days'] = [
        '#markup' => $time_remaining,
      ];
    }
    else{
      $time_remaining = 'Audit Date Not Released.';
      $form['days'] = [
        '#markup' => $time_remaining,
      ];
    }
    $node->save();
    return $form;
  }

}
