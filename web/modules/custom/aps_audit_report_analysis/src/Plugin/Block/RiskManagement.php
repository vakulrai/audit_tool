<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'RiskManagement' block.
 *
 * @Block(
 *  id = "risk_management",
 *  admin_label = @Translation("Risk management"),
 * )
 */
class RiskManagement extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build = [];
    $first_last_date_monthly = [];
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    
    $build['risk_management_fieldset']['risk_management'] = [
      '#type' => 'fieldset',
    ];
    $build['risk_management_fieldset']['risk_management']['#attached']['library'][] = 'aps_audit_report_analysis/aps_dashboard_risk_management_js';
    // $build['risk_management_fieldset']['risk_management']['#attached']['drupalSettings']['auditor_data'] = json_encode($plot_data['data']);
    // $build['risk_management_fieldset']['risk_management']['#attached']['drupalSettings']['total_user'] = $first_last_date_monthly['total_user'];
    // $build['risk_management_fieldset']['risk_management']['#attached']['drupalSettings']['selected_user_count'] = $first_last_date_monthly['selected_user_count'];
    $build['risk_management_fieldset']['risk_management']['container_element_risk_eport']['#markup'] = '<div id="container-element-risk-report" style="min-width: 150px; height: 400px; max-width: 400px; margin: 0 auto">HELLO</div>';

    //Get data for Finding categories minor/major.
    $risk_data = [];
    $risk_data['findings']['count_audits'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$uri[1].'?field_finding_categories_target_id=major&field_finding_categories_target_id=minor'));
    $risk_data['findings']['major'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$uri[1].'?field_finding_categories_target_id=major&type=auditor_report'));
    $risk_data['findings']['minor'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$uri[1].'?field_finding_categories_target_id=minor&type=auditor_report'));
    
    $risk_data['findings']['no_of_department'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$uri[1].'?type=department'));
    //Logic for calculating score.
    $total_marks_obtained = 0;
    if($risk_data['findings']['major']>0){
      $total_marks_obtained += $risk_data['findings']['major'] * 5;
    }
    else{
      $total_marks_obtained += $risk_data['findings']['major'] * 0;
    }

    if($risk_data['findings']['minor'] > 0){
      $total_marks_obtained += $risk_data['findings']['minor'] * 3;
    }
    else{
      $total_marks_obtained += $risk_data['findings']['minor'] * 0;
    }

    if($risk_data['findings']['count_audits'] >= $risk_data['findings']['no_of_department']){
      $risk_category = 'MEDIUM';
      $score = 3;
    }
    elseif ($risk_data['findings']['count_audits'] <= $risk_data['findings']['no_of_department']) {
      $risk_category = 'LOW';
      $score = 1;
    }
    else{
      $risk_category = 'HIGH';
      $score = 5;
    }

    $build['findings'] = [
      '#type' => 'details', 
      '#title' => t('Findings'), 
      '#attributes' => ['id' => 'findings'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['qualifications'] = [
      '#type' => 'details', 
      '#title' => t('Qualifications'), 
      '#attributes' => ['id' => 'qualifications'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['scheduling'] = [
      '#type' => 'details', 
      '#title' => t('Scheduling'), 
      '#attributes' => ['id' => 'scheduling'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $header = [
      t('MARKS OBTAINED.'),
      t('RISK CATEGORY'),
      t('INCIDENCE'),
      t('RISK SCORE'),
    ];
    $build['title_data'] = [
      '#type' => 'item',
      '#markup' => '<h1>FINDINGS</h1>',
    ];

    $build['findings']['tableselect_element'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => t('No content available.'),
    ];

    $build['findings']['tableselect_element'][0]['obtained_marks'] = [
      '#markup' => 'Deviations<br>(Total):'.$total_marks_obtained,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element'][0]['risk_cat'] = [
      '#markup' => $risk_category,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element'][0]['incidence'] = [
      '#markup' => $score,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element'][0]['risk_score'] = [
      '#markup' => $score * $total_marks_obtained,
      '#title_display' => 'invisible',
    ];

    $build['findings']['risk_score_details'] = [
      '#type' => 'details', 
      '#title' => t('View Score Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['findings']['risk_score_details']['data'] = [
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
    $query->condition('n.field_refere_target_id', $uri[1]);
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
    $build['findings']['tableselect_element_imp_points'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => t('No content available.'),
    ];

    $build['findings']['tableselect_element_imp_points'][0]['obtained_marks_improvement'] = [
      '#markup' => 'Improvement Points<br>'.$total,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element_imp_points'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $risk_category_improvement,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element_imp_points'][0]['incidence_improvement'] = [
      '#markup' => $score_improvement,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element_imp_points'][0]['risk_score_improvement'] = [
      '#markup' => $get_max_count * $score_improvement,
      '#title_display' => 'invisible',
    ];

    $build['findings']['risk_score_details_improvement'] = [
      '#type' => 'details', 
      '#title' => t('View Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['findings']['risk_score_details_improvement']['data_improvement'] = [
      '#markup' => '<b>Improvement - Quality</b>  :'.$quality.'</br><b>Improvement - Cost</b>  : '.$cost.'<br><b>No Improvement Point</b>  : '.$no_improvement.'<br><b>Improvement - Productivity</b>  : '.$productivity.'<br><b>Procedural Related</b>  : '.$procedural,
    ];

    //Adherence to schedule.
    $reschedule_risk_category = '';
    $reschedule_count = 0;
    $score_improvement_adherence = 0;
    $total_schedule_reschedule = 0;
    $risk_data['adherence']['rescheduled'] = count(getAuditOPtions('risk_managemant','/get-moderation-export/'.$uri[1].'?type=planned_events&moderation=workflow_for_audit_planning-reschedule'));
    
    $risk_data['adherence']['scheduled'] = count(getAuditOPtions('risk_managemant','/get-moderation-export/'.$uri[1].'?type=planned_events&moderation=workflow_for_audit_planning-scheduled'));

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
    
    // if($risk_data['adherence']['rescheduled'] > $risk_data['adherence']['scheduled']){
    //   $score_improvement_adherence = $risk_data['adherence']['rescheduled'];
    // }
    // else{
    //   $score_improvement_adherence = $risk_data['adherence']['scheduled'];
    // }
    $build['findings']['tableselect_element_rescheduled'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => t('No content available.'),
    ];

    $build['findings']['tableselect_element_rescheduled'][0]['obtained_marks_improvement'] = [
      '#markup' => 'adherence to Reschedule<br>'.$total_schedule_reschedule,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element_rescheduled'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $reschedule_risk_category,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element_rescheduled'][0]['incidence_improvement'] = [
      '#markup' => $risk_count,
      '#title_display' => 'invisible',
    ];

    $build['findings']['tableselect_element_rescheduled'][0]['risk_score_improvement'] = [
      '#markup' => $total_schedule_reschedule * $risk_count,
      '#title_display' => 'invisible',
    ];

    $build['findings']['risk_score_details_rescheduled'] = [
      '#type' => 'details', 
      '#title' => t('View Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['findings']['risk_score_details_rescheduled']['data_improvement'] = [
      '#markup' => '<b>No rescheduling incidences due to Auditor</b>  :'.$risk_data['adherence']['scheduled'].'</br><b>Reported incidences of rescheduling due to Auditor </b>  : '.$risk_data['adherence']['rescheduled'],
    ];

    //QUALIFICATIONS
    $count_auditor = 0;
    $risk_data['qualifications']['auditor7'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?field_score_value_greater=7'));
    $risk_data['qualifications']['auditor6'] = count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?field_score_value_greater=6'));
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

    $build['qualifications']['tableselect_element_qualifications'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => t('No content available.'),
    ];

    $build['qualifications']['tableselect_element_qualifications'][0]['obtained_marks_improvement'] = [
      '#markup' => 'Score<br>'.$count_auditor,
      '#title_display' => 'invisible',
    ];

    $build['qualifications']['tableselect_element_qualifications'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $qual_risk_category,
      '#title_display' => 'invisible',
    ];

    $build['qualifications']['tableselect_element_qualifications'][0]['incidence_improvement'] = [
      '#markup' => $qual_risk_score,
      '#title_display' => 'invisible',
    ];

    $build['qualifications']['tableselect_element_qualifications'][0]['risk_score_improvement'] = [
      '#markup' => $count_auditor * $qual_risk_score,
      '#title_display' => 'invisible',
    ];

    $build['qualifications']['risk_score_details_qualifications'] = [
      '#type' => 'details', 
      '#title' => t('View Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['qualifications']['risk_score_details_qualifications']['data_improvement'] = [
      '#markup' => '<b>75% of Auditors in the Qualified Auditor list with a score of 75%</b>  :'.count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?field_score_value_greater=7')).'</br><b>at least one auditor for each listed Function with a score of >60%</b>  : '.count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?field_score_value_greater=6')).'<br><b>All other categories </b>  : '.count(getAuditOPtions('auditor_selection','/auditor-and-audit-export/'.$uri[1].'?field_score_value=6')),
    ];

    //Audit Performance.
    $risk_data['ap']['get_total_sections'] = getAuditOPtions('ap','/get-moderation-export/'.$uri[1].'?type=section');
    $risk_data['ap']['get_total_events'] = $this->getsectionOPtions('auditor_selection','/get-moderation-export/'.$uri[1].'?type=planned_events');
    $count_covered_sections = 0;
    $count_uncovered_sections = 0;
    $total_section = 0;
    foreach ($risk_data['ap']['get_total_sections'] as $section_key => $section_value) {
      $total_section++;
      if(array_key_exists($section_key, $risk_data['ap']['get_total_events'])){
        $count_covered_sections++;
      }
      else{
        $count_uncovered_sections++;
      }
    }
    $ap_total_all = 0;
    $ap_total_one = 0;
    $ap_total_more_one = 0;
    if($count_covered_sections){
      $ap_total_all = 3;
    }
    elseif ($count_uncovered_sections == 1) {
      $ap_total_one = 1;
    }
    elseif ($count_uncovered_sections > 1) {
      $ap_total_more_one = 0;
    }
    $ap_total = $ap_total_all + $ap_total_one + $ap_total_more_one;
    if($ap_total == 3){
      $ap_risk_cat = 'HIGH';
      $ap_score = 5;
    }
    elseif ($ap_total == 1) {
      $ap_risk_cat = 'MEDIUM';
      $ap_score = 3;
    }elseif ($ap_total == 0) {
      $ap_risk_cat = 'LOW';
      $ap_score = 1;
    }
    else{
      $ap_score = 0;
    }

    $build['scheduling']['ap']['ap_tableselect'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => t('No content available.'),
    ];

    $build['scheduling']['ap']['ap_tableselect'][0]['obtained_marks_improvement'] = [
      '#markup' => 'Scheduling<br>'.$ap_total,
      '#title_display' => 'invisible',
    ];

    $build['scheduling']['ap']['ap_tableselect'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $ap_risk_cat,
      '#title_display' => 'invisible',
    ];

    $build['scheduling']['ap']['ap_tableselect'][0]['incidence_improvement'] = [
      '#markup' => $ap_score,
      '#title_display' => 'invisible',
    ];

    $build['scheduling']['ap']['ap_tableselect'][0]['risk_score_improvement'] = [
      '#markup' => $ap_score * $ap_total,
      '#title_display' => 'invisible',
    ];

    $build['scheduling']['ap']['ap_details'] = [
      '#type' => 'details', 
      '#title' => t('View Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['scheduling']['ap']['ap_details']['data_improvement'] = [
      '#markup' => '<b>All sections covered </b>  :'.$ap_total_all.'* 5'.'</br><b>1 section missed out </b>  : '.$ap_total_one.'* 3'.'<br><b>More then 1 section missed out </b>  : '.$ap_total_more_one.'* 1',
    ];

    return $build;
  }

  function getsectionOPtions($type, $url) {
    global $base_url;
    if($type){
      $client = \Drupal::httpClient();
      $request = $client->get($base_url.$url);
      $response = $request->getBody();
      $data = json_decode($response);
      foreach ($data as $key => $value) {
        $options[$value->field_sections] = $value->title;
      }
    }
    return $options;
}

}