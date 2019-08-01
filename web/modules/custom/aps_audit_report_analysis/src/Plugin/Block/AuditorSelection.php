<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DateHelper;

/**
 * Provides a 'AuditorSelection' block.
 *
 * @Block(
 *  id = "auditor_selection",
 *  admin_label = @Translation("Auditor selection"),
 * )
 */
class AuditorSelection extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $first_last_date_monthly = [];
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $first_day_this_month = date('01-01-Y');
    $last_day_this_month  = date('m-t-Y');
    $get_all_months_name = DateHelper::monthNamesUntranslated();
    $current_month = date('n');
    $user_count = 0;
    $selected_user_count = 0;
    $total = 0;
    foreach ($get_all_months_name as $key => $month_name) {
      if($key<=$current_month){
	      $format_for_first_day = 'Y-'. $key . '-01';
	      $format_for_last_day = 'Y-m-t';
	      $first_last_date_monthly[$month_name]['first_day'] = date($format_for_first_day);
	      $first_last_date_monthly[$month_name]['last_day'] = date($format_for_last_day, strtotime(date($format_for_first_day)));
	      $first_last_date_monthly[$month_name]['count_underscore_auditor'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?field_start_date_value[min]='.timestampFromDate($first_last_date_monthly[$month_name]['first_day']).'&field_start_date_value[max]='.timestampFromDate($first_last_date_monthly[$month_name]['last_day']).'&field_score_value=6'));
        
	      $first_last_date_monthly[$month_name]['audit_count'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?field_start_date_value[min]='.timestampFromDate($first_last_date_monthly[$month_name]['first_day']).'&field_start_date_value[max]='.timestampFromDate($first_last_date_monthly[$month_name]['last_day'])));

	      $selected_user_count = $first_last_date_monthly[$month_name]['count_underscore_auditor'];
	      $first_last_date_monthly[$month_name]['selected_user_count'] = $selected_user_count;
	      $plot_data['data'][] = [$first_last_date_monthly[$month_name]['count_underscore_auditor']];
          $total += $first_last_date_monthly[$month_name]['count_underscore_auditor'];
          $first_last_date_monthly['total'] = $total;
	    }
    }
    
    $user_count_query = \Drupal::database()->select('user__field_reference_id', 'n');
    $user_count_query->fields('n', ['field_reference_id_target_id','entity_id']);
    $user_count_query->condition('n.bundle', 'user');
    $user_count_query->condition('n.field_reference_id_target_id', $uri[1]);
    $users = $user_count_query->execute()->fetchAll();
    $user_count = count($users);

    $query = \Drupal::database()->select('user__field_reference_id', 'n');
    $query->join('node__field_auditor', 'rf', 'n.entity_id = rf.field_auditor_target_id');
    $query->fields('n', ['entity_id']);
    $query->fields('rf', ['entity_id']);
    $query->condition('n.bundle', 'user');
    $query->condition('n.field_reference_id_target_id', $uri[1]);
    $unique_users_audit = $query->execute()->fetchAll();
    foreach ($unique_users_audit as $key => $value) {
      $user_[$value->entity_id] = $value->rf_entity_id;
    }
    $unique_user_ids = count(array_unique($user_));
    $build['audit_auditor_report']['auditor_report'] = [
      '#type' => 'fieldset',
    ];
    $build['audit_auditor_report']['auditor_report'] ['#attached']['library'][] = 'aps_audit_report_analysis/aps_dashboard_auditor_report_js';
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['auditor_data'] = json_encode($plot_data['data']);
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['total_user'] = $user_count;
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['selected_user_count'] = $unique_user_ids;
    $build['audit_auditor_report']['auditor_report']['container_element_audit_auditor_report']['#markup'] = '<div id="container-element-auditor-report" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>';

    $selected_user = $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['selected_user_count'];
    $total_user = $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['total_user'];
    $percentage_selection = ($selected_user / $total_user * 100);
    $chart_title = $percentage_selection.'% Auditor Selection';
    return $build;
  }

}
