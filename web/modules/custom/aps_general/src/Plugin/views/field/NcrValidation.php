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
 * @ViewsField("ncr_audit_validate")
 */
class NcrValidation extends FieldPluginBase {

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
    $unit_reference = $node->get('field_refere')->target_id;
    $event_start_date_timestamp = $node->get('field_end_date')->value;
    $audit_cycle_settings = getAuditCycleObjectCurrentUnit($unit_reference);
    if(isset($audit_cycle_settings->get('field_car_release_by_auidtee_')->value)){
      $days_before_event = $audit_cycle_settings->get('field_car_release_by_auidtee_')->value;
    }
    else{
      $days_before_event = 0;
    }
    $audit_cycle_time = date('Y-m-d H:i:s',strtotime('+'.$days_before_event.'day', $event_start_date_timestamp));
    $date1 = new \DateTime($audit_cycle_time);
    $date2 = new \DateTime();
    $diff = $date2->diff($date1);
    $months = $diff->m;
    $days = $diff->days; 
    $hours = $diff->h;
    $check_invert_time = $diff->invert;
    $total_hours = $days * 24 + $hours;

    if ($total_hours > 0  && $check_invert_time != 1) {
      if(isset($node->field_report_reference->target_id)){
      	$report_id = $node->field_report_reference->target_id;
        $form['add_delta_qa'] = [
	      '#type' => 'link',
	      '#title' => t('NCR'),
	      '#url' => Url::fromRoute('entity.node.edit_form',['node' => $report_id,'event_reference' => $node->id(), 'unit_reference' => $unit_reference]),
	      '#suffix' => '<br><p class="event-date"><b>Note:<br>Last day to Submit NCR is: '. $audit_cycle_time.'</b></p>',
        ];
	  }
	  else{
	  	$form['add_delta_qa'] = [
	      '#type' => 'markup',
	      '#markup' => t('Audit Not submitted.'),
        ];
	  }
    }
    else{
       $form['add_delta_qa'] = [
          '#markup' => 'Last Date of NCR was :<b>'.$audit_cycle_time.'</b>',
        ];
    }
    return $form;
  }

}
