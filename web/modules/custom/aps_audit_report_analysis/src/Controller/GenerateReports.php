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
                  <th>'.$this->t('MARKS OBTAINED.').'</th>
                  <th>'.$this->t('RISK CATEGORY: ').'</th>
                  <th>'.$this->t('INCIDENCE: ').'</th>
                  <th>'.$this->t('RISK SCORE: ').'</th>
                  <th>'.$this->t('Details: ').'</th>
              </tr>
          </thead>';
        foreach ($risk_data['findings'] as $risk_data_key => $risk_data_value) {
          $html .= '<tr>';
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
    foreach ($get_all_months_name as $key => $month_name) {
      if($key<=$current_month){
        $format_for_first_day = 'Y-'. $key . '-01';
        $format_for_last_day = 'Y-m-t';
        $first_last_date_monthly[$month_name]['first_day'] = date($format_for_first_day);
        $first_last_date_monthly[$month_name]['last_day'] = date($format_for_last_day, strtotime(date($format_for_first_day)));
        $first_last_date_monthly[$month_name]['count_underscore_auditor'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?created[min]='.$first_last_date_monthly[$month_name]['first_day'].'&created[max]='.$first_last_date_monthly[$month_name]['last_day'].'&field_score_value=6'));
        $first_last_date_monthly[$month_name]['audit_count'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?created[min]='.$first_last_date_monthly[$month_name]['first_day'].'&created[max]='.$first_last_date_monthly[$month_name]['last_day']));

        $user_count += count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?created[min]='.$first_last_date_monthly[$month_name]['first_day'].'&created[max]='.$first_last_date_monthly[$month_name]['last_day']));
        $selected_user_count += $first_last_date_monthly[$month_name]['count_underscore_auditor'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?created[min]='.$first_last_date_monthly[$month_name]['first_day'].'&created[max]='.$first_last_date_monthly[$month_name]['last_day'].'&field_score_value=6'));

        $first_last_date_monthly['total_user'] = $user_count;
        $first_last_date_monthly['selected_user_count'] = $selected_user_count;
        $plot_data['data'][] = [$first_last_date_monthly[$month_name]['count_underscore_auditor']];
      }
    }
    $build['audit_auditor_report']['auditor_report'] = [
      '#type' => 'fieldset',
    ];
    $build['audit_auditor_report']['auditor_report'] ['#attached']['library'][] = 'aps_audit_report_analysis/aps_dashboard_auditor_report_js';
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['auditor_data'] = json_encode($plot_data['data']);
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['total_user'] = $first_last_date_monthly['total_user'];
    $build['audit_auditor_report']['auditor_report']['#attached']['drupalSettings']['selected_user_count'] = $first_last_date_monthly['selected_user_count'];
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
    $build = [];
    $build = [];
    $first_last_date_monthly = [];
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    
    $risk_data = [];
    $risk_data['count_audits'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$unit_reference.'?field_finding_categories_target_id=major&field_finding_categories_target_id=minor'));
    $risk_data['major'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$unit_reference.'?field_finding_categories_target_id=major&type=auditor_report'));
    $risk_data['minor'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$unit_reference.'?field_finding_categories_target_id=minor&type=auditor_report'));
    
    $risk_data['no_of_department'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$unit_reference.'?type=department'));
    //Logic for calculating score.
    $total_marks_obtained = 0;
    if($risk_data['major']>0){
      $total_marks_obtained += $risk_data['major'] * 5;
    }
    else{
      $total_marks_obtained += $risk_data['major'] * 0;
    }

    if($risk_data['minor'] > 0){
      $total_marks_obtained += $risk_data['minor'] * 3;
    }
    else{
      $total_marks_obtained += $risk_data['minor'] * 0;
    }

    if($risk_data['count_audits'] >= $risk_data['no_of_department']){
      $risk_category = 'MEDIUM';
      $score = 3;
    }
    elseif ($risk_data['count_audits'] <= $risk_data['no_of_department']) {
      $risk_category = 'LOW';
      $score = 1;
    }
    else{
      $risk_category = 'HIGH';
      $score = 5;
    }

    $build['findings'][0]['obtained_marks'] = [
      '#markup' => 'Deviations<br>(Total):'.$total_marks_obtained,
      '#title_display' => 'invisible',
    ];

    $build['findings'][0]['risk_cat'] = [
      '#markup' => $risk_category,
      '#title_display' => 'invisible',
    ];

    $build['findings'][0]['incidence'] = [
      '#markup' => $score,
      '#title_display' => 'invisible',
    ];

    $build['findings'][0]['risk_score'] = [
      '#markup' => $score * $total_marks_obtained,
      '#title_display' => 'invisible',
    ];

    $build['findings'][0]['data'] = [
      '#markup' => '<b>Minor</b>  :  3*'.$risk_data['findings']['minor'].'</br><b>Major</b>  :  5*'.$risk_data['findings']['major'].'<br><b>None</b>  :  0*0',
    ];
    
    //Improvement Point.
    $query = \Drupal::database()->select('node__field_refere', 'n');
    $query->join('node_field_data', 'nfd', 'n.entity_id = nfd.nid');
    $query->join('node__field_audit_list', 'ls', 'nfd.nid = ls.entity_id');
    $query->join('paragraphs_item_field_data', 'f', 'ls.field_audit_list_target_id = f.id');
    $query->join('paragraph__field_finding_categories', 'fc', 'f.id = fc.entity_id');
    $query->fields('n',['entity_id', 'bundle', 'field_refere_target_id']);
    $query->fields('f',['id']);
    $query->fields('fc',['field_finding_categories_target_id']);
    $query->condition('n.bundle', 'auditor_report');
    $query->condition('n.field_refere_target_id', $unit_reference);
    $nids = $query->execute()->fetchAll();

    $quality = 0;
    $cost = 0;
    $productivity = 0;
    $no_improvement = 0;
    $procedural = 0;
    $total = 0;
    $risk_category_improvement = '';
    $score_improvement = 0;
    $detail_string = '';
    $count_no_of_frequency = [];
    $key_quality = 0;
    $key_cost = 0;
    $key_productivity= 0;
    $key_procedural = 0;
    $key_no = 0;
    foreach ($nids as $key => $value) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($value->field_finding_categories_target_id);
      $names = $term->name->value;
      if($names == 'Improvement - Quality'){
        $total += 3 * count($term->tid->value);
        $quality = $total;
        $key_quality += count($term->tid->value);
        $count_no_of_frequency[$names] = $key_quality;
      }
      elseif ($names == 'Improvement - Cost') {
        $total += 5 * count($term->tid->value);
        $cost = $total;
        $key_cost += count($term->tid->value);
        $count_no_of_frequency[$names] = $key_cost;
      }
      elseif ($names == 'Improvement - Productivity') {
        $total += 5 * count($term->tid->value);
        $productivity = $total;
        $key_productivity += count($term->tid->value);
        $count_no_of_frequency[$names] = $key_productivity;
      }
      elseif ($names == 'Procedural Related') {
        $total += 2 *count($term->tid->value);
        $procedural = $total;
        $key_procedural += count($term->tid->value);
        $count_no_of_frequency[$names] = $key_procedural;
      }
      else{
        if($names != 'Major' || $names != 'Minor'){
          $total += 0 * count($term->tid->value);
          $no_improvement = 0;
          $key_no += count($term->tid->value);
          $count_no_of_frequency[$names] = $key_no;
        }
      }
      if($names == 'No Improvement Point'){
        $risk_category_improvement = 'HIGH';
        $score_improvement = 5;
      }
      elseif ($names == 'Procedural Related' && $names == 'Improvement - Quality') {
        $risk_category_improvement = 'MEDIUM';
        $score_improvement = 3;
      }
      elseif ($names == 'Improvement - Cost' || $names == 'Improvement - Productivity') {
        $risk_category_improvement = 'LOW';
        $score_improvement = 1;
      }
      else{
        $risk_category_improvement = 'Not Found';
        $score_improvement = 0;
      }
    }
    $maxs = array_keys($count_no_of_frequency, max($count_no_of_frequency));
    $get_max_count = $count_no_of_frequency[$maxs[0]];

    $build['findings'][1]['obtained_marks'] = [
      '#markup' => 'Improvement Points<br>'.$total,
      '#title_display' => 'invisible',
    ];

    $build['findings'][1]['risk_cat'] = [
      '#markup' => $risk_category_improvement,
      '#title_display' => 'invisible',
    ];

    $build['findings'][1]['incidence'] = [
      '#markup' => $score_improvement,
      '#title_display' => 'invisible',
    ];

    $build['findings'][1]['risk_score'] = [
      '#markup' => $get_max_count * $score_improvement,
      '#title_display' => 'invisible',
    ];

    $build['findings'][1]['data'] = [
      '#markup' => '<b>Improvement - Quality</b>  :'.$quality.'</br><b>Improvement - Cost</b>  : '.$cost.'<br><b>No Improvement Point</b>  : '.$no_improvement.'<br><b>Improvement - Productivity</b>  : '.$productivity.'<br><b>Procedural Related</b>  : '.$procedural,
    ];

    //Adherence to schedule.
    $reschedule_risk_category = '';
    $reschedule_count = 0;
    $score_improvement_adherence = 0;
    $total_schedule_reschedule = 0;
    $risk_data['adherence']['rescheduled'] = count(getAuditOPtions('risk_managemant','/get-moderation-export/'.$unit_reference.'?type=planned_events&moderation=workflow_for_audit_planning-reschedule'));
    
    $risk_data['adherence']['scheduled'] = count(getAuditOPtions('risk_managemant','/get-moderation-export/'.$unit_reference.'?type=planned_events&moderation=workflow_for_audit_planning-scheduled'));

    if($risk_data['adherence']['rescheduled'] > 0){
      $reschedule_count += 0;
      $total_schedule_reschedule = $reschedule_count;
    }
    if($risk_data['adherence']['scheduled'] > 0){
      $reschedule_count += 3;
      $total_schedule_reschedule = $reschedule_count;
    }

    if($reschedule_count <= 3){
      $reschedule_risk_category = 'HIGH';
      $risk_count = 5;
    }
    elseif ($reschedule_count == 6) {
      $reschedule_risk_category = 'MEDIUM';
      $risk_count = 3;
    }
    elseif ($reschedule_count == 9) {
      $reschedule_risk_category = 'LOW';
      $risk_count = 1;
    }
    
    $build['findings'][2]['obtained_marks'] = [
      '#markup' => 'adherence to Reschedule<br>'.$total_schedule_reschedule,
      '#title_display' => 'invisible',
    ];

    $build['findings'][2]['risk_cat'] = [
      '#markup' => $reschedule_risk_category,
      '#title_display' => 'invisible',
    ];

    $build['findings'][2]['incidence'] = [
      '#markup' => $risk_count,
      '#title_display' => 'invisible',
    ];

    $build['findings'][2]['risk_score'] = [
      '#markup' => $total_schedule_reschedule * $risk_count,
      '#title_display' => 'invisible',
    ];

    $build['findings'][2]['data'] = [
      '#markup' => '<b>No rescheduling incidences due to Auditor</b>  :'.$risk_data['adherence']['scheduled'].'</br><b>Reported incidences of rescheduling due to Auditor </b>  : '.$risk_data['adherence']['rescheduled'],
    ];

    //QUALIFICATIONS
    $count_auditor = 0;
    $risk_data['qualifications']['auditor7'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?field_score_value_greater=7'));
    $risk_data['qualifications']['auditor6'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?field_score_value_greater=6'));
    if($risk_data['qualifications']['auditor7'] > 0){
       $count_auditor += 3;
    }
    elseif($risk_data['qualifications']['auditor6'] == 1 || $risk_data['qualifications']['auditor6'] > 1){
      $count_auditor += 1;
    }
    else{
     $count_auditor += 0;
    }
    
    if($count_auditor == 3){
      $qual_risk_category = 'LOW';
      $qual_risk_score = 1;
    }
    elseif($count_auditor == 1){
      $qual_risk_category = 'MEDIUM';
      $qual_risk_score = 3;
    }else{
       $qual_risk_category = 'HIGH';
       $qual_risk_score = 5;
    }

    $build['findings'][3]['obtained_marks'] = [
      '#markup' => 'Score<br>'.$count_auditor,
      '#title_display' => 'invisible',
    ];

    $build['findings'][3]['risk_cat'] = [
      '#markup' => $qual_risk_category,
      '#title_display' => 'invisible',
    ];

    $build['findings'][3]['incidence'] = [
      '#markup' => $qual_risk_score,
      '#title_display' => 'invisible',
    ];

    $build['findings'][3]['risk_score'] = [
      '#markup' => $count_auditor * $qual_risk_score,
      '#title_display' => 'invisible',
    ];

    $build['findings'][3]['data'] = [
      '#markup' => '<b>75% of Auditors in the Qualified Auditor list with a score of 75%</b>  :'.count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?field_score_value_greater=7')).'</br><b>at least one auditor for each listed Function with a score of >60%</b>  : '.count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?field_score_value_greater=6')).'<br><b>All other categories </b>  : '.count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$unit_reference.'?field_score_value=6')),
    ];
    return $build;
  }
}
