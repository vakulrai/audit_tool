<?php

namespace Drupal\aps_general\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

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
    $days_before_event = $audit_cycle_settings->get('field_rescheduling_of_dates_')->value;
    $audit_cycle_time = date('Y-m-d',strtotime('-'.$days_before_event.'day', $event_start_date_timestamp));
    $diff = strtotime($audit_cycle_time) - time();
    $years = floor($diff / (365*60*60*24));
    $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
    $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24)); 
    $hours = floor(($diff - $years * 365*60*60*24  - $months*30*60*60*24 - $days*60*60*24) / (60*60));
    if($node->get('moderation_state')->value == 'release_audit'){
      $time_remaining = $days .' Days '. $hours . ' Hour';
      $form['days'] = [
        '#markup' => $time_remaining,
        '#suffix' => '<br><p class="event-date"><b>Note:<br>Last day to Reschedule Event is: '. $audit_cycle_time.'</b></p>',
      ];
      $node->set('field_pre_audit_status', 'intime');
    }
    else{
      $time_remaining = 'Audit Date Has been Passed Last date Was: '.$event_start_date_timestamp;
      $form['days'] = [
        '#markup' => $time_remaining,
      ];
      $node->set('field_pre_audit_status', 'notintime');
    }
    $node->save();
    return $form;
  }

}
