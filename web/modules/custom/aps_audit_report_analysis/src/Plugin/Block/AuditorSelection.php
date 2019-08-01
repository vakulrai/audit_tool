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
    foreach ($get_all_months_name as $key => $month_name) {
      if($key<=$current_month){
	      $format_for_first_day = 'Y-'. $key . '-01';
	      $format_for_last_day = 'Y-m-t';
	      $first_last_date_monthly[$month_name]['first_day'] = date($format_for_first_day);
	      $first_last_date_monthly[$month_name]['last_day'] = date($format_for_last_day, strtotime(date($format_for_first_day)));
	      $first_last_date_monthly[$month_name]['count_underscore_auditor'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?created[min]='.$first_last_date_monthly[$month_name]['first_day'].'&created[max]='.$first_last_date_monthly[$month_name]['last_day'].'&field_score_value=6'));
	      $first_last_date_monthly[$month_name]['audit_count'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?created[min]='.$first_last_date_monthly[$month_name]['first_day'].'&created[max]='.$first_last_date_monthly[$month_name]['last_day']));

	      $selected_user_count += $first_last_date_monthly[$month_name]['count_underscore_auditor'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?created[min]='.$first_last_date_monthly[$month_name]['first_day'].'&created[max]='.$first_last_date_monthly[$month_name]['last_day'].'&field_score_value=6'));

	      // $first_last_date_monthly['total_user'] = $user_count;
	      $first_last_date_monthly['selected_user_count'] = $selected_user_count;
	      $plot_data['data'][] = [$first_last_date_monthly[$month_name]['count_underscore_auditor']];
	    }
    }
    $query = \Drupal::database()->select('user__field_reference_id', 'n');
    $query->fields('n', ['field_reference_id_target_id','entity_id']);
    $query->condition('n.bundle', 'user');
    $query->condition('n.field_reference_id_target_id', $uri[1]);
    $users = $query->execute()->fetchAll();
    $user_count = count($users);

    $build['audit_auditor_report']['auditor_report'] = [
      '#type' => 'fieldset',
    ];
    $build['audit_auditor_report']['auditor_report'] ['#attached']['library'][] = 'aps_audit_report_analysis/aps_dashboard_auditor_report_js';
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['auditor_data'] = json_encode($plot_data['data']);
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['total_user'] = $user_count;
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['selected_user_count'] = $first_last_date_monthly['selected_user_count'];
    $build['audit_auditor_report']['auditor_report']['container_element_audit_auditor_report']['#markup'] = '<div id="container-element-auditor-report" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>';

    $selected_user = $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['selected_user_count'];
    $total_user = $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['total_user'];
    $percentage_selection = ($selected_user / $total_user * 100);
    $chart_title = $percentage_selection.'% Auditor Selection';
    return $build;
  }

}
