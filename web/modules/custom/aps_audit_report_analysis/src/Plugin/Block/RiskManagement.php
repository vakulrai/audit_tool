<?php

namespace Drupal\aps_audit_report_analysis\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\paragraphs\Entity\Paragraph;

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
    $risk_scale_array = [];
    $build['risk_management_fieldset']['risk_management'] = [
      '#type' => 'fieldset',
    ];

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

    $build['kpi'] = [
      '#type' => 'details', 
      '#title' => t('KPI targets of sections and Audit findings '), 
      '#attributes' => ['id' => 'kpi'], 
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

    $build['performance'] = [
      '#type' => 'details', 
      '#title' => t('Conclusion of Audit Cycle'), 
      '#attributes' => ['id' => 'performance'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $header_findings = [
      t('NUMBER'),
      t('MARKS OBTAINED.'),
      t('RISK CATEGORY'),
      t('INCIDENCE'),
      t('RISK SCORE'),
    ];

     $header = [
      t('MARKS OBTAINED.'),
      t('RISK CATEGORY'),
      t('INCIDENCE'),
      t('RISK SCORE'),
    ];

    $build['risk_management_fieldset']['risk_management']['#attached']['library'][] = 'aps_audit_report_analysis/aps_dashboard_risk_management_js';
    // $build['risk_management_fieldset']['risk_management']['#attached']['drupalSettings']['auditor_data'] = json_encode($plot_data['data']);
    // $build['risk_management_fieldset']['risk_management']['#attached']['drupalSettings']['total_user'] = $first_last_date_monthly['total_user'];
    // $build['risk_management_fieldset']['risk_management']['#attached']['drupalSettings']['selected_user_count'] = $first_last_date_monthly['selected_user_count'];
    $build['risk_management_fieldset']['risk_management']['container_element_risk_legend']['#markup'] = '<div id="container-element-risk-legend" style="min-width: 150px; height: 400px; max-width: 400px; margin: 0 auto"></div>';
    $build['risk_management_fieldset']['risk_management']['container_element_risk_report']['#markup'] = '<div id="container-element-risk-report" style="min-width: 150px; height: 400px; max-width: 400px; margin: 0 auto"></div>';

    //Get data for Finding categories minor/major.
    $risk_data = [];
    $risk_data['findings']['major'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$uri[1].'?field_finding_categories_target_id=major&type=auditor_report'));
    $risk_data['findings']['minor'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$uri[1].'?field_finding_categories_target_id=minor&type=auditor_report'));

    $risk_data['findings']['no_of_department'] = count(getAuditOPtions('risk_managemant','/risk-report-export/'.$uri[1].'?type=department'));
    //Get Frequency form Audit criteria settings.
    $frequency = getListofMonths($uri[1]);
    //Logic for calculating score.
    $total_marks_obtained = 0;
    $finding_percentage_minor = 0;
    $finding_percentage_major = 0;
    $total_major_minor = 0;
    $total_major_minor = $risk_data['findings']['major'] + $risk_data['findings']['minor'];
    if($risk_data['findings']['major']>0){
      $total_marks_obtained += $risk_data['findings']['major'] * 5;
      $finding_percentage_major  = $total_marks_obtained;
    }
    else{
      $total_marks_obtained += $risk_data['findings']['major'] * 0;
      $finding_percentage_major  = $total_marks_obtained;
    }

    if($risk_data['findings']['minor'] > 0){
      $total_marks_obtained += $risk_data['findings']['minor'] * 3;
      $finding_percentage_minor  = $total_marks_obtained;
    }
    else{
      $total_marks_obtained += $risk_data['findings']['minor'] * 0;
      $finding_percentage_minor  = $total_marks_obtained;
    }
   
    if($total_marks_obtained != 0){
      $major = $risk_data['findings']['major'];
      $minor = $risk_data['findings']['minor'];
      if($major != 0){
        $finding_percentage_major = ($major/ $total_marks_obtained * 100);
          $risk_category = 'LOW';
          $score = 1;
      }
      else{
        if($minor != 0){
          $finding_percentage_minor = ($minor/ $total_marks_obtained * 100);
          if($finding_percentage_minor == 50){
            $risk_category = 'MEDIUM';
            $score = 3;
          }
        }
        else{
          $risk_category = 'HIGH';
          $score = 5;
        }
      }
    }
    else{
      $risk_category = 'HIGH';
      $score = 5;
    }

    $risk_scale_array[] = $risk_category;
    $build['findings']['tableselect_element'] = [
      '#type' => 'table',
      '#header' => $header_findings,
      '#prefix' => '<div class=table-responsive-wrapper>',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['table-responsive']],
      '#empty' => t('No content available.'),
    ];
    
    $build['findings']['tableselect_element'][0]['each_score'] = [
      '#markup' => '<b>Minor</b>  :  3*'.$risk_data['findings']['minor'].'</br><b>Major</b>  :  5*'.$risk_data['findings']['major'].'<br><b>None</b>  :  0*0',
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'NUMBER'],
    ];

    $build['findings']['tableselect_element'][0]['obtained_marks'] = [
      '#markup' => 'Deviations<br>(Total):'.$total_marks_obtained,
      '#title_display' => 'invisible',
       '#wrapper_attributes' => ['data-label' => 'MARKS OBTAINED.'],
    ];

    $build['findings']['tableselect_element'][0]['risk_cat'] = [
      '#markup' => $risk_category,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK CATEGORY'],
    ];

    $build['findings']['tableselect_element'][0]['incidence'] = [
      '#markup' => $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'INCIDENCE'],
    ];

    $build['findings']['tableselect_element'][0]['risk_score'] = [
      '#markup' => $score * $frequency,
      '#title_display' => 'invisible',
       '#wrapper_attributes' => ['data-label' => 'RISK SCORE'],
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
    $query->join('paragraph__field_kpi_status', 'kpi', 'fc.entity_id = kpi.entity_id');
    $query->fields('n',['entity_id', 'bundle', 'field_refere_target_id']);
    $query->fields('f',['id']);
    $query->fields('fc',['field_finding_categories_target_id']);
    $query->fields('kpi',['field_kpi_status_value']);
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
    $kpi = 0;
    $kpi_total = 0;
    $count_improvement_implemented = 0;
    $count_improvement_not_implemented = 0;
    foreach ($nids as $key => $value) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($value->field_finding_categories_target_id);
      $load_item = Paragraph::load($value->id);
      $names = $term->name->value;
      if($names == 'Improvement - Quality'){
        $quality_no = count($term->tid->value);
        $total += 3 * count($term->tid->value);
        $quality = $total;
        $key_quality += count($term->tid->value);
        $count_no_of_frequency[$names] = $key_quality;
        $names_array[$names] = $names;
        if($load_item->field_car_status->value == 'implemented'){
          $count_improvement_implemented++;
        }
        elseif ($load_item->field_car_status->value == 'not-implemented') {
          $count_improvement_not_implemented++;
        }
        $car_status[$load_item->field_car_status->value] = $load_item->field_car_status->value;
      }
      elseif ($names == 'Improvement - Cost') {
        $cost_no = count($term->tid->value);
        $total += 5 * count($term->tid->value);
        $cost = $total;
        $key_cost += count($term->tid->value);
        $count_no_of_frequency[$names] = $key_cost;
        $names_array[$names] = $names;
        if($load_item->field_car_status->value == 'implemented'){
          $count_improvement_implemented++;
        }
        elseif ($load_item->field_car_status->value == 'not-implemented') {
          $count_improvement_not_implemented++;
        }
        $car_status[$load_item->field_car_status->value] = $load_item->field_car_status->value;
      }
      elseif ($names == 'Improvement - Productivity') {
        $prod_no = count($term->tid->value);
        $total += 5 * count($term->tid->value);
        $productivity = $total;
        $key_productivity += count($term->tid->value);
        $count_no_of_frequency[$names] = $key_productivity;
        $names_array[$names] = $names;
        if($load_item->field_car_status->value == 'implemented'){
          $count_improvement_implemented++;
        }
        elseif ($load_item->field_car_status->value == 'not-implemented') {
          $count_improvement_not_implemented++;
        }
        $car_status[$load_item->field_car_status->value] = $load_item->field_car_status->value;
      }
      elseif ($names == 'Procedural Related') {
        $procedural_no = count($term->tid->value);
        $total += 2 *count($term->tid->value);
        $procedural = $total;
        $key_procedural += count($term->tid->value);
        $count_no_of_frequency[$names] = $key_procedural;
        $names_array[$names] = $names;
      }
      elseif ($names == 'No Improvement Point') {
        if($names == 'No Improvement Point' && $value->field_kpi_status_value == 'achieved'){
          $kpi_total += 1;
        }
        else{
          $kpi_total += 0;
        }
      }
      elseif ($names == 'Minor') {
        if($names == 'Minor' && $value->field_kpi_status_value == 'achieved'){
          $kpi_total += 1;
        }
        else{
          $kpi_total += 0;
        }
      }
      elseif ($names == 'Major') {
        if($names == 'Major' && $value->field_kpi_status_value == 'achieved'){
          $kpi_total += 3;
        }
        else{
          $kpi_total += 0;
        }
      }
      else{
        if($names != 'Major' || $names != 'Minor'){
          $total += 0 * count($term->tid->value);
          $no_improvement = 0;
          $key_no += count($term->tid->value);
          $count_no_of_frequency[$names] = $key_no;
        }
      }
      
      //Risk Prameter for KPI.
      if($kpi_total == 0){
        $risk_category_improvement = 'HIGH';
        $kpi = 5;
      }
      elseif ($kpi_total == 1) {
        $risk_category_improvement = 'MEDIUM';
        $kpi = 3;
      }
      elseif ($kpi_total == 3) {
        $risk_category_improvement = 'LOW';
        $kpi = 1;
      }
    }
    
    $risk_scale_array[] = $risk_category_improvement;
    if($names_array['No Improvement Point']){
        $names_array[$names] = $names;
        $risk_category_improvement = 'HIGH';
        $score_improvement = 5;
    }
    elseif ($names_array['Procedural Related'] && $names_array['Improvement - Quality']) {
      $risk_category_improvement = 'MEDIUM';
      $score_improvement = 3;
    }
    elseif ($names_array['Improvement - Cost'] || $names_array['Improvement - Productivity']) {
      $risk_category_improvement = 'LOW';
      $score_improvement = 1;
    }
    else{
      $risk_category_improvement = 'Not Found';
      $score_improvement = 0;
    }
    $maxs = array_keys($count_no_of_frequency, max($count_no_of_frequency));
    $get_max_count = $count_no_of_frequency[$maxs[0]];
    $build['findings']['tableselect_element_imp_points'] = [
      '#type' => 'table',
      '#header' => $header_findings,
      '#prefix' => '<div class=table-responsive-wrapper>',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['table-responsive']],
      '#empty' => t('No content available.'),
    ];
    
    $build['findings']['tableselect_element_imp_points'][0]['each_score'] = [
      '#markup' => '<b>Improvement - Quality</b> '.$quality_no.'</br><b>Improvement - Cost</b>  : '.$cost_no.'<br><b>No Improvement Point</b> '.$no_improvement.'<br><b>Improvement - Productivity</b> '.$prod_no.'<br><b>Procedural Related</b> '.$procedural_no,
       '#wrapper_attributes' => ['data-label' => 'NUMBER'],
    ];


    $build['findings']['tableselect_element_imp_points'][0]['obtained_marks_improvement'] = [
      '#markup' => 'Improvement Points<br>'.$total,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'MARKS OBTAINED.'],
    ];

    $build['findings']['tableselect_element_imp_points'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $risk_category_improvement,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK CATEGORY'],
    ];

    $build['findings']['tableselect_element_imp_points'][0]['incidence_improvement'] = [
      '#markup' => $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'INCIDENCE'],
    ];

    $build['findings']['tableselect_element_imp_points'][0]['risk_score_improvement'] = [
      '#markup' => $frequency * $score_improvement,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK SCORE'],
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

    if($reschedule_count < 3){
      $reschedule_risk_category = 'HIGH';
      $risk_count = 5;
    }
    elseif ($reschedule_count == 3) {
      $reschedule_risk_category = 'MEDIUM';
      $risk_count = 3;
    }
    elseif ($reschedule_count >=3) {
      $reschedule_risk_category = 'LOW';
      $risk_count = 1;
    }
    
    $risk_scale_array[] = $reschedule_risk_category;
    // if($risk_data['adherence']['rescheduled'] > $risk_data['adherence']['scheduled']){
    //   $score_improvement_adherence = $risk_data['adherence']['rescheduled'];
    // }
    // else{
    //   $score_improvement_adherence = $risk_data['adherence']['scheduled'];
    // }
    $build['findings']['tableselect_element_rescheduled'] = [
      '#type' => 'table',
      '#header' => $header,
      '#prefix' => '<div class=table-responsive-wrapper>',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['table-responsive']],
      '#empty' => t('No content available.'),
    ];

    $build['findings']['tableselect_element_rescheduled'][0]['obtained_marks_improvement'] = [
      '#markup' => 'adherence to Reschedule<br>'.$total_schedule_reschedule,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'MARKS OBTAINED.'],
    ];

    $build['findings']['tableselect_element_rescheduled'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $reschedule_risk_category,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK CATEGORY'],
    ];

    $build['findings']['tableselect_element_rescheduled'][0]['incidence_improvement'] = [
      '#markup' => $risk_count,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'INCIDENCE'],
    ];

    $build['findings']['tableselect_element_rescheduled'][0]['risk_score_improvement'] = [
      '#markup' => $risk_count * $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK SCORE'],
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
    
    $risk_scale_array[] = $qual_risk_category;
    $build['qualifications']['tableselect_element_qualifications'] = [
      '#type' => 'table',
      '#header' => $header,
      '#prefix' => '<div class=table-responsive-wrapper>',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['table-responsive']],
      '#empty' => t('No content available.'),
    ];

    $build['qualifications']['tableselect_element_qualifications'][0]['obtained_marks_improvement'] = [
      '#markup' => 'Score<br>'.$count_auditor,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'MARKS OBTAINED.'],
    ];

    $build['qualifications']['tableselect_element_qualifications'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $qual_risk_category,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK CATEGORY'],
    ];

    $build['qualifications']['tableselect_element_qualifications'][0]['incidence_improvement'] = [
      '#markup' => $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'INCIDENCE'],
    ];

    $build['qualifications']['tableselect_element_qualifications'][0]['risk_score_improvement'] = [
      '#markup' => $count_auditor * $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK SCORE'],
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

    //Timely release as calculated.
    $risk_data['schedule_release']['#title'] = 'Timely Release as Calculated';
    $risk_data['schedule_release']['intime'] = count(getAuditOPtions('schedule_release','/get-moderation-export/'.$uri[1].'?type=planned_events&release_status=intime'));
    $risk_data['schedule_release']['notinintime'] = count(getAuditOPtions('schedule_release','/get-moderation-export/'.$uri[1].'?type=planned_events&release_status=notinintime'));
    $total_score_release = 0;
    $intime_score = 0;
    $notintime_score = 0;
    $not_asper_target = 0;
    if($risk_data['schedule_release']['intime'] > 0){
      $intime_score += 1;
    }
    else{
      $not_asper_target = 0;
    }
    if($risk_data['schedule_release']['notinintime'] > 0){
      $notintime_score += 3;
    }
    else{
      $not_asper_target = 0;
    }
    
    $total_score_release = $intime_score + $notintime_score ;
    
    if($total_score_release == 3){
      $risk_level = 'LOW';
      $risk_score = 3;
    }
    elseif($total_score_release == 1){
      $risk_level = 'MEDIUM';
      $risk_score = 3;
    }
    elseif($total_score_release == 0){
      $risk_level = 'HIGH';
      $risk_score = 5;
    }
    
    $risk_scale_array[] = $risk_level;
    $build['scheduling']['audit_release']['tableselect'] = [
      '#type' => 'table',
      '#header' => $header,
      '#prefix' => '<div class=table-responsive-wrapper>',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['table-responsive']],
      '#empty' => t('No content available.'),
    ];

    $build['scheduling']['audit_release']['tableselect'][0]['obtained_marks_improvement'] = [
      '#markup' => 'Timely release as calculated<br>'.$total_score_release,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'MARKS OBTAINED.'],
    ];

    $build['scheduling']['audit_release']['tableselect'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $risk_level,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK CATEGORY'],
    ];

    $build['scheduling']['audit_release']['tableselect'][0]['incidence_improvement'] = [
      '#markup' => $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'INCIDENCE'],
    ];

    $build['scheduling']['audit_release']['tableselect'][0]['risk_score_improvement'] = [
      '#markup' => $frequency * $total_score_release,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK SCORE'],
    ];

    $build['scheduling']['audit_release']['details'] = [
      '#type' => 'details', 
      '#title' => t('View Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['scheduling']['audit_release']['details']['data_improvement'] = [
      '#markup' => '<b>as per target </b>  :'.$intime_score.'</br><b> exceeded target </b>  : '.$notintime_score.'<br><b>not as per target </b>  : '.$not_asper_target,
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
    
    $risk_scale_array[] = $ap_risk_cat;
    $build['scheduling']['ap']['ap_tableselect'] = [
      '#type' => 'table',
      '#header' => $header,
      '#prefix' => '<div class=table-responsive-wrapper>',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['table-responsive']],
      '#empty' => t('No content available.'),
    ];

    $build['scheduling']['ap']['ap_tableselect'][0]['obtained_marks_improvement'] = [
      '#markup' => 'coverage of all sections in Audit cycle<br>'.$ap_total,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'MARKS OBTAINED.'],
    ];

    $build['scheduling']['ap']['ap_tableselect'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $ap_risk_cat,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK CATEGORY'],
    ];

    $build['scheduling']['ap']['ap_tableselect'][0]['incidence_improvement'] = [
      '#markup' => $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'INCIDENCE'],
    ];

    $build['scheduling']['ap']['ap_tableselect'][0]['risk_score_improvement'] = [
      '#markup' => $frequency * $ap_total,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK SCORE'],
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

    //Get data for KPI.
    $build['kpi']['tableselect_element_imp_points'] = [
      '#type' => 'table',
      '#header' => $header,
      '#prefix' => '<div class=table-responsive-wrapper>',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['table-responsive']],
      '#empty' => t('No content available.'),
    ];

    $build['kpi']['tableselect_element_imp_points'][0]['obtained_marks_improvement'] = [
      '#markup' => 'Improvement Points<br>'.$kpi_total,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'MARKS OBTAINED.'],
    ];

    $build['kpi']['tableselect_element_imp_points'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $risk_category_improvement,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK CATEGORY'],
    ];

    $build['kpi']['tableselect_element_imp_points'][0]['incidence_improvement'] = [
      '#markup' => $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'INCIDENCE'],
    ];

    $build['kpi']['tableselect_element_imp_points'][0]['risk_score_improvement'] = [
      '#markup' => $kpi_total * $frequency,
      '#title_display' => 'invisible',
      '#wrapper_attributes' => ['data-label' => 'RISK SCORE'],
    ];

    $build['kpi']['risk_score_details_improvement'] = [
      '#type' => 'details', 
      '#title' => t('View Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['kpi']['risk_score_details_improvement']['data_improvement'] = [
      '#markup' => '<b>KPI achieved and  with No Improvement Findings </b>  :'.$kpi_total.'</br><b>KPI not achieved and  with No Improvement Findings </b>  : '.$kpi_total.'<br><b>KPI achieved and with Minor nonconformity Findings </b>  : '.$kpi_total.'<br><b>KPI not achieved and  with Minor Non conformity  Findings </b>  : '.$kpi_total.'<br><b>KPI achieved and with Major non conformity Findings </b>  : '.$kpi_total.'<br><b>KPI not achieved and with Major Non Findings</b>  : '.$kpi_total,
    ];

    //Get data for Audit Performance - Improvement actions implementation.
    $total_implemented_notimplemented = $count_improvement_implemented + $count_improvement_not_implemented;
    $not_implemented = 0;
    $greater_than_50 = ($count_improvement_implemented / $total_implemented_notimplemented) * 100;
    
    if($car_status['implemented']){
        $names_array[$names] = $names;
        if($greater_than_50 > 50){
          $risk_category_performance = 'MEDIUM';
          $score_performance = 3;
          $total_implemented_notimplemented_score = 3 * $count_improvement_implemented;
        }
        else{
          $risk_category_performance = 'LOW';
          $score_performance = 1;
          $total_implemented_notimplemented_score = 1 * $count_improvement_implemented;
        }
    }
    elseif ($car_status['not-implemented']) {
      $risk_category_performance = 'HIGH';
      $score_performance = 5;
      $total_implemented_notimplemented_score = 0 * $count_improvement_not_implemented;
    }
    else{
      $risk_category_performance = 'Not Found';
      $score_performance = 0;
      $total_implemented_notimplemented_score = 0;
    }
    
    $risk_scale_array[] = $risk_category_performance;
    $build['performance']['tableselect_element_performance'] = [
      '#type' => 'table',
      '#header' => $header_findings,
      '#empty' => t('No content available.'),
    ];
    
    $build['performance']['tableselect_element_performance'][0]['each_score'] = [
      '#markup' => '<b>Implemented </b>  :'.$count_improvement_implemented.'</br><b>50% Implemented</b>  : '.($count_improvement_implemented / $total_implemented_notimplemented).'<br><b>Not Implemented </b>  : '.$count_improvement_not_implemented,
    ];

    $build['performance']['tableselect_element_performance'][0]['obtained_marks_improvement'] = [
      '#markup' => 'Improvement Points: <br>'.$total_implemented_notimplemented_score,
      '#title_display' => 'invisible',
    ];

    $build['performance']['tableselect_element_performance'][0]['risk_cat_dev_improvement'] = [
      '#markup' => $risk_category_performance,
      '#title_display' => 'invisible',
    ];

    $build['performance']['tableselect_element_performance'][0]['incidence_improvement'] = [
      '#markup' => $frequency,
      '#title_display' => 'invisible',
    ];

    $build['performance']['tableselect_element_performance'][0]['risk_score_improvement'] = [
      '#markup' => $score_performance * $frequency,
      '#title_display' => 'invisible',
    ];

    $build['performance']['risk_score_details_improvement'] = [
      '#type' => 'details', 
      '#title' => t('View Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['performance']['risk_score_details_improvement']['data_improvement'] = [
      '#markup' => '<b>Implemented </b>  :'.$count_improvement_implemented.'</br><b>50% Implemented</b>  : '.($count_improvement_implemented / $total_implemented_notimplemented).'<br><b>Not Implemented </b>  : '.$count_improvement_not_implemented,
    ];

    //Get data for Audit Performance - Checklist additions.
    $checlist_delta_added_total = 0;
    $check_deltaQ_added_to_checklist = count(getAuditOPtions('auditor_selection','/checklist-addition/'.$uri[1].'?field_answer_type_value=delta'));
    $check_non_delta_added_to_checklist = count(getAuditOPtions('auditor_selection','/checklist-addition/'.$uri[1].'?field_answer_type_value=non-delta'));
    
    if($check_deltaQ_added_to_checklist > 0){
      $checlist_delta_added_total = $check_deltaQ_added_to_checklist * 3;
    }
    else{
      $checlist_delta_added_total = $check_non_delta_added_to_checklist * 0;
    }

    if($checlist_delta_added_total == 3){
      $checklist_score = 1;
      $checklist_scale = 'LOW';
    }
    elseif($checlist_delta_added_total == 0){
      $checklist_score = 5;
      $checklist_scale = 'HIGH';
    }
    else{
      $checklist_score = 0;
      $checklist_scale = 'Not Applicable';
    }
    
    $risk_scale_array[] = $checklist_scale;
    $build['performance']['tableselect_element_checklist'] = [
      '#type' => 'table',
      '#header' => $header_findings,
      '#empty' => t('No content available.'),
    ];
    
    $build['performance']['tableselect_element_checklist'][1]['each_score'] = [
      '#markup' => '<b>Auditor recommendations from the audit cycle approved and checklist revised </b>  :'.$check_deltaQ_added_to_checklist.'</br><b>Auidtor recommendations from the audit cycle approved and checklist not revised</b>  : '.$check_non_delta_added_to_checklist,
    ];

    $build['performance']['tableselect_element_checklist'][1]['obtained_marks_improvement'] = [
      '#markup' => 'Improvement Points: <br>'.$checlist_delta_added_total,
      '#title_display' => 'invisible',
    ];

    $build['performance']['tableselect_element_checklist'][1]['risk_cat_dev_improvement'] = [
      '#markup' => $checklist_scale,
      '#title_display' => 'invisible',
    ];

    $build['performance']['tableselect_element_checklist'][1]['incidence_improvement'] = [
      '#markup' => $frequency,
      '#title_display' => 'invisible',
    ];

    $build['performance']['tableselect_element_checklist'][1]['risk_score_improvement'] = [
      '#markup' => $checklist_score * $frequency,
      '#title_display' => 'invisible',
    ];

    $build['performance']['risk_score_details_checklist'] = [
      '#type' => 'details', 
      '#title' => t('View Details'), 
      '#attributes' => ['id' => 'display'], 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];

    $build['performance']['risk_score_details_checklist']['data_improvement'] = [
      '#markup' => '<b>Auditor recommendations from the audit cycle approved and checklist revised </b>  :'.$check_deltaQ_added_to_checklist.'</br><b>Auidtor recommendations from the audit cycle approved and checklist not revised</b>  : '.$check_non_delta_added_to_checklist,
    ];
    
    $count_report = count($risk_scale_array);
    $risk_category_count = array_count_values($risk_scale_array);
    $risk_category_['high'] = $risk_category_count['HIGH'];
    $risk_category_['medium']= $risk_category_count['MEDIUM'];
    $risk_category_['low'] = $risk_category_count['LOW'];
    $maxs_risk = array_keys($risk_category_, max($risk_category_));
    $get_max_risk = $risk_category_[$maxs_risk[0]];
    $total_risk_percentage = ($get_max_risk / $count_report) * 100;

    $build['risk_management']['#attached']['drupalSettings']['risk_percentage'] = $total_risk_percentage;
    $build['risk_management']['#attached']['drupalSettings']['risk_type'] = strtoupper($maxs_risk[0]);
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