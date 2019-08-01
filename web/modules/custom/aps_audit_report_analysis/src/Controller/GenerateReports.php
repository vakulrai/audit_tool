<?php

namespace Drupal\aps_audit_report_analysis\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use Drupal\Core\Datetime\DateHelper;
use Drupal\aps_audit_report_analysis\Plugin\Block\RiskManagement;

/**
 * Controller routines for aps_audit_report_analysis routes.
 */
class GenerateReports extends ControllerBase {

  public function generateHTMLReports($report_type,$unit_reference) {
    $output = $this->getDataforHTML($report_type, $unit_reference);
    $options = new Options();
    $options->set('isRemoteEnabled', TRUE);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($output);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream();
    $file_name = "Audit Reporting";
    $dompdf->get_canvas()->page_text(370, 570, "Page: {PAGE_NUM} of {PAGE_COUNT}", null, 12, array(0,0,0));
    $dompdf->stream($file_name,["Attachment" => 1]);
  }

  public function getDataforHTML($report_type, $unit_reference){
    global $base_url;
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $query = \Drupal::database()->select('audit_cycle__field_unit_reference', 'h');
    $query->fields('h',['entity_id']);
    $query->condition('h.field_unit_reference_target_id', $unit_reference);
    $query->range(0, 1);
    $nids = $query->execute()->fetchAll();
    $node_storage = \Drupal::entityManager()->getStorage('audit_cycle');
    $entity_audit_cycle = $node_storage->load($nids[0]->entity_id);
    if(count($entity_audit_cycle)){
      $cycle_type = $entity_audit_cycle->get('field_cycle_type')->value;
      if($cycle_type == 0){
        $cycle_type_name = 'Financial';
        $audit_cycle_start_date = $entity_audit_cycle->get('field_financial_dates')->value;
        $audit_cycle_end_date = $entity_audit_cycle->get('field_financial_dates')->end_value;
      }
      elseif ($cycle_type == 1) {
        $cycle_type_name = 'Calendar';
        $audit_cycle_start_date = $entity_audit_cycle->get('field_calendar_date')->value;
        $audit_cycle_end_date = $entity_audit_cycle->get('field_calendar_date')->end_value;
      }
    }
    $html = '<html>';
    $html .= '<head>';
    $html .= '<style>
    header {
      position: fixed;
      top: -60px;
      left: 0px;
      right: 0px;
      border-bottom: 2px solid #0aa7c6;
      padding-bottom:-5px;
      color: #000;
    }
    .logo_section {
      float: left;
      margin-top: 1.8em;
      width: 20%;
      display: inline-block;
    }
    .title {
      width: 80%;
      display: inline-block;
      margin-top: 0.9em;
      text-align: center;
      color: #000;
      font-family: "RobotoBold",sans-serif;
    }';
    $html .= '</style>';
    $html .= '</head>';
    $html .= '<header>
    <h1 class ="title">'.$report_name.'</h1>';
    $html .= '</header>';

    // Audit cycle related .
    $html .= '<div class="consumption-userinfo">';

    // First Section.
    $html .= '<div class="infowrap" style="width:100%;">';
      // Name
    $html .= '<div class="audit-cycle-cycle">'.$this->t('<span class="label">Cycle Type: </span>').'<span class="customer-info">'.$this->t($cycle_type_name).'</span></div>';

    $html .= '<div class="audit-cycle-date-start">'.$this->t('<span class="label">Start Date: </span>').'<span class="start-info">'.$this->t($audit_cycle_start_date).'</span></div>';
    // Account ID.
    $html .= '<div class="audit-cycle-end-start">'.$this->t('<span class="label">End Date: </span>').'<span class="end-info">'.$this->t($audit_cycle_end_date).'</span></div>';
    $html .= '</div>';
    $html .= '</br>';

    //********************************//
    if($report_type == 'activity_related'){
      $this->getAuditorGraph($unit_reference);
      $html .= '<h1>Annual calendar plan</h1>';
      //Detail: A *****Get Annual calendar plan****//.
      $data_planned_event = $this->getdatafromuri('event','/ncr-car-management-details/'.$unit_reference.'?type=planned_events');
      if(count($data_planned_event)){
        $html .= '<table id = "annual-planned-events" style="width:100%;">
          <thead>
              <tr>
                  <th>'.$this->t('Sl No.').'</th>
                  <th>'.$this->t('Title: ').'</th>
                  <th>'.$this->t('Start Date: ').'</th>
                  <th>'.$this->t('End Date: ').'</th>
              </tr>
          </thead>';
        $planned_count = 0;
        foreach ($data_planned_event as $data_planned_event_key => $data_planned_event_value) {
          $html .= '<tr>';
          $html .= '<td>' . $planned_count . '</td>';
          $html .= '<td>' . $data_planned_event_value['title'] . '</td>';
          $html .= '<td>' . $data_planned_event_value['start_date'] . '</td>';
          $html .= '<td>' . $data_planned_event_value['end_date'] . '</td>';
          $html .= '</tr>';
          $planned_count++;
        }
        $html .= '</table>';
      }

      $html .= '</br>';
      $html .= '<h1>List of Qualitifed Auditors </h1>';

      //Detail: B *****List of Qualitifed Auditors ****//.
      $auditor_chart_path = drupal_get_path('module', 'aps_audit_report_analysis') . '/highchart-images-pdf/Auditor-Performance.jpeg';
      $data_qualified_auditors = $this->getdatafromuri('auditor','/auditor-and-audit-export/'.$unit_reference.'?field_score_value_greater=5');
      if(count($data_qualified_auditors)){
        $html .= '<table id = "qualified-auditors" style="width:100%;">
          <thead>
              <tr>
                  <th>'.$this->t('Sl No.').'</th>
                  <th>'.$this->t('Name: ').'</th>
                  <th>'.$this->t('Score: ').'</th>
                  <th>'.$this->t('Function: ').'</th>
              </tr>
          </thead>';
        $qualified_auditors_count = 0;
        foreach ($data_qualified_auditors as $data_qualified_auditors_key => $data_qualified_auditors_value) {
          $html .= '<tr>';
          $html .= '<td>' . $qualified_auditors_count . '</td>';
          $html .= '<td>' . $data_qualified_auditors_value['name'] . '</td>';
          $html .= '<td>' . $data_qualified_auditors_value['score'] . '</td>';
          $html .= '<td>' . $data_qualified_auditors_value['function'] . '</td>';
          $html .= '</tr>';
          $qualified_auditors_count++;
        }
        $html .= '</table>';
      }
      else{
        $html .= '<table id = "qualified-auditors" style="width:100%;">
          <thead>
              <tr>
                  <th>'.$this->t('Sl No.').'</th>
                  <th>'.$this->t('Name: ').'</th>
                  <th>'.$this->t('Score: ').'</th>
                  <th>'.$this->t('Function: ').'</th>
              </tr>
          </thead>';
        $html .= '<tfoot>
            <tr>
                <th></th>
                <th></th>
                <th><b>No content Found</b></th>
                <th></th>
            </tr>
        </tfoot><br><br>';

      }
      $html .= '</br>';
      $html .= ' <img src='.$auditor_chart_path.' height="700" width="450"> ';
      $html .= '<h1>List of Process/Product/Business Process </h1>';

      //Detail: B *****List of Process ****//.
      $data_process = $this->getdatafromuri('process','/get-registration-data/'.$unit_reference.'?type[]=assembly&type[]=manufacturing_process&type[]=customers_manual&type[]=customers_manual&type[]=business_process');
      if(count($data_process)){
        $html .= '<table id = "qualified-auditors" style="width:100%;">
          <thead>
              <tr>
                  <th>'.$this->t('Sl No.').'</th>
                  <th>'.$this->t('Name: ').'</th>
                  <th>'.$this->t('Type: ').'</th>
                  <th>'.$this->t('Unit Name: ').'</th>
              </tr>
          </thead>';
        $process_count = 0;
        foreach ($data_process as $data_process_key => $data_process_value) {
          $html .= '<tr>';
          $html .= '<td>' . $process_count . '</td>';
          $html .= '<td>' . $data_process_value['title'] . '</td>';
          $html .= '<td>' . $data_process_value['type'] . '</td>';
          $html .= '<td>' . $data_process_value['unit_name'] . '</td>';
          $html .= '</tr>';
          $process_count++;
        }
        $html .= '</table>';
      }
    }
    elseif($report_type == 'risk'){
      $risk_data = $this->getDataRiskManagement($unit_reference, null);
      $html .= '<h1>Risk Management</h1>';
      //Detail: A *****Risk::Findings****//.
      if(count($risk_data)){
        $risk = 0;
        $html .= '<table id = "risk-management" style="width:100%;">
          <thead>
              <tr>
                  <th>'.$this->t('NUMBER.').'</th>
                  <th>'.$this->t('MARKS OBTAINED.').'</th>
                  <th>'.$this->t('RISK CATEGORY: ').'</th>
                  <th>'.$this->t('INCIDENCE: ').'</th>
                  <th>'.$this->t('RISK SCORE: ').'</th>
                  <th>'.$this->t('Details: ').'</th>
              </tr>
          </thead>';
        foreach ($risk_data['risk_report'] as $risk_data_key => $risk_data_value) {
          if(count($risk_data_value['each_score'])){
            $each_score = $risk_data_value['each_score']['#markup'];
          }
          else{
            $each_score = 'N/A';
          }
          $html .= '<tr>';
          $html .= '<td>' . $each_score . '</td>';
          $html .= '<td>' . $risk_data_value['obtained_marks']['#markup'] . '</td>';
          $html .= '<td>' . $risk_data_value['risk_cat']['#markup'] . '</td>';
          $html .= '<td>' . $risk_data_value['incidence']['#markup'] . '</td>';
          $html .= '<td>' . $risk_data_value['risk_score']['#markup'] . '</td>';
          $html .= '<td>' . $risk_data_value['data']['#markup'] . '</td>';
          $html .= '</tr>';
          $risk++;
        }
        $html .= '</table>';
      }
    }
    $html .="</html>";
    return $html;
  }

  public function getAuditorGraph($unit_reference){
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
        $first_last_date_monthly[$month_name]['count_underscore_auditor'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?field_start_date_value[min]='.timestampFromDate($first_last_date_monthly[$month_name]['first_day']).'&field_start_date_value[max]='.timestampFromDate($first_last_date_monthly[$month_name]['last_day']).'&field_score_value=6'));
        
        $first_last_date_monthly[$month_name]['audit_count'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?field_start_date_value[min]='.timestampFromDate($first_last_date_monthly[$month_name]['first_day']).'&field_start_date_value[max]='.timestampFromDate($first_last_date_monthly[$month_name]['last_day'])));

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
    $user_count_query->condition('n.field_reference_id_target_id', $unit_reference);
    $users = $user_count_query->execute()->fetchAll();
    $user_count = count($users);

    $query = \Drupal::database()->select('user__field_reference_id', 'n');
    $query->join('node__field_auditor', 'rf', 'n.entity_id = rf.field_auditor_target_id');
    $query->fields('n', ['entity_id']);
    $query->fields('rf', ['entity_id']);
    $query->condition('n.bundle', 'user');
    $query->condition('n.field_reference_id_target_id', $unit_reference);
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
    getHighchartImageExportforPDF($plot_data['data'], 'Underscore User', $chart_title, 'line', 'auditor_report');
    return $build;
  }

  public function getdatafromuri($type, $url) {
    global $base_url;
    if($type){
      $client = \Drupal::httpClient();
      $request = $client->get($base_url.$url);
      $response = $request->getBody();
      $data = json_decode($response);
      foreach ($data as $key => $value) {
        if($type == 'event'){
          $options[$value->nid]['title'] = $value->title;
          $options[$value->nid]['start_date']  = $value->field_start_date;
          $options[$value->nid]['end_date']  = $value->field_end_date;
        }
        elseif($type == 'auditor'){
          $options[$value->uid]['name'] = $value->name;
          $options[$value->uid]['score']  = $value->field_score;
          $options[$value->uid]['function']  = $value->field_functions_qualified;
        }
        elseif($type == 'process'){
          $options[$value->nid]['title'] = $value->title;
          $options[$value->nid]['type']  = $value->type;
          $options[$value->nid]['unit_name']  = $value->unit_name;
        }
      }
    }
    return $options;
  }

  public function getDataRiskManagement($unit_reference,$key){
   foreach (RiskManagement::build($unit_reference) as $risk_type => $report_data) {
      $risk_data[$risk_type] = $report_data;
      unset($risk_data['risk_management']);
      unset($risk_data['risk_management_fieldset']);
    }
    $risk_report['risk_report'][0] = $risk_data['findings']['tableselect_element']['data-0'];
    $risk_report['risk_report'][1] = $risk_data['findings']['tableselect_element_imp_points']['data-1'];
    $risk_report['risk_report'][2] = $risk_data['findings']['tableselect_element_rescheduled']['data-2'];
    $risk_report['risk_report'][3] = $risk_data['qualifications']['tableselect_element_qualifications']['data-3'];
    $risk_report['risk_report'][4] = $risk_data['scheduling']['audit_release']['tableselect']['data-4'];
    $risk_report['risk_report'][5] = $risk_data['scheduling']['ap']['ap_tableselect']['data-5'];
    $risk_report['risk_report'][6] = $risk_data['kpi']['tableselect_element_imp_points']['data-6'];
    $risk_report['risk_report'][7] = $risk_data['performance']['tableselect_element_performance']['data-7'];
    $risk_report['risk_report'][8] = $risk_data['performance']['tableselect_element_checklist']['data-8'];
    return $risk_report;
  }
}
