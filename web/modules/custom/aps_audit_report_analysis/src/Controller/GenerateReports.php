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
use Drupal\aps_pre_audit\Form\UpdateAuditFindings;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\taxonomy\Entity\Term;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Controller routines for aps_audit_report_analysis routes.
 */
class GenerateReports extends ControllerBase {

  public function generateHTMLReports($report_type,$start_date, $end_date, $unit_reference) {
    $response = new AjaxResponse();
    $output = $this->getDataforHTML($report_type, $start_date, $end_date, $unit_reference);
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
    return $response;
  }

  public function getDataforHTML($report_type, $start_date, $end_date, $unit_reference){
    global $base_url;
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $date_range_query = '&range[min]='.$start_date.'&range[max]='.$end_date.'';
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
        $audit_cycle_start_date = $entity_audit_cycle->get('field_financial_dates')->value ? $entity_audit_cycle->get('field_financial_dates')->value : date('Y-m-d');
        $audit_cycle_end_date = $entity_audit_cycle->get('field_financial_dates')->end_value ? $entity_audit_cycle->get('field_financial_dates')->end_value : date('Y-m-d');
      }
      elseif ($cycle_type == 1) {
        $cycle_type_name = 'Calendar';
        $audit_cycle_start_date = $entity_audit_cycle->get('field_calendar_date')->value != '' ? $entity_audit_cycle->get('field_calendar_date')->value : date('Y-m-d');
        $audit_cycle_end_date = $entity_audit_cycle->get('field_calendar_date')->end_value != '' ? $entity_audit_cycle->get('field_calendar_date')->end_value : date('Y-m-d');
      }
    }

    $html = '<html>';
    $html .= '<head>';
    $html .= '<style>
    header {
      height: 64px;
      z-index: 30;
      color: #FFF;
      text-align: center;
      position: relative;
      width: 100%;
      border-bottom: 2px solid #607d8b;
      display: block;
      margin-bottom: 20px;
    }
    .title {
      display: block;
      padding: 0;
      font-size: 18px;
      font-weight: 700;
      color: #607d8b;
      margin-top: 0;
      margin-bottom: 0;
      text-transform: uppercase;
      font-family: "RobotoBold",sans-serif;
    }
    ';
    $html .= '</style>';
    $html .= '</head>';
    $html .= '<header>
    <h1 class ="title">Unit '.Node::load($unit_reference)->title->value.'</h1>';
    $html .= '</header>';

    // Audit cycle related .
    $html .= '<div class="consumption-userinfo">';

    // First Section.
    $html .= '<div class="infowrap" style="width:100%; ">';
      // Name
    $html .= '<div class="audit-cycle-cycle">'.$this->t('<span class="label">Cycle Type: </span>').'<span class="customer-info">'.$cycle_type_name.'</span></div>';

    $html .= '<div class="audit-cycle-date-start">'.$this->t('<span class="label">Start Date: </span>').'<span class="start-info">'.$audit_cycle_start_date.'</span></div>';
    // Account ID.
    $html .= '<div class="audit-cycle-end-start">'.$this->t('<span class="label">End Date: </span>').'<span class="end-info">'.$audit_cycle_end_date.'</span></div>';
    $html .= '</div>';
    $html .= '</br>';

    //********************************//
    if($report_type == 'annual_calendar_plan'){
      // $this->getAuditorGraph($unit_reference);
      $html .= '<h1>Annual calendar plan</h1>';
      //Detail: A *****Get Annual calendar plan****//.
      $data_planned_event = $this->getdatafromuri('event','/ncr-car-management-details/'.$unit_reference.'?type[]=planned_events'.$date_range_query);
      echo '<pre>';
      if(count($data_planned_event)){
        $html .= '<table id = "annual-planned-events" style="width:100%;box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24); border-collapse: collapse;">
          <thead>
              <tr style="border-bottom: 1px solid black;">
                  <th>'.$this->t('Sl No.').'</th>
                  <th>'.$this->t('Title: ').'</th>
                  <th>'.$this->t('Audit Type').'</th>
                  <th>'.$this->t('Selected Auditor').'</th>
                  <th>'.$this->t('Selected Auditee').'</th>
                  <th>'.$this->t('Start Date: ').'</th>
                  <th>'.$this->t('End Date: ').'</th>
              </tr>
          </thead>';
        $planned_count = 0;
        foreach ($data_planned_event as $data_planned_event_key => $data_planned_event_value) {
          $html .= '<tr  style="border-bottom: 1px solid #e0e0e0;">';
          $html .= '<td>' . $planned_count . '</td>';
          $html .= '<td>' . $data_planned_event_value['title'] . '</td>';
          $html .= '<td>' . $data_planned_event_value['field_audit_type'] . '</td>';
          $html .= '<td>' . $data_planned_event_value['field_auditor'] . '</td>';
          $html .= '<td>' . $data_planned_event_value['field_select_auditee'] . '</td>';
          $html .= '<td>' . $data_planned_event_value['start_date'] . '</td>';
          $html .= '<td>' . $data_planned_event_value['end_date'] . '</td>';
          $html .= '</tr>';
          $planned_count++;
        }
        $html .= '</table>';
      }

      // $html .= '</br>';
      // $html .= '<h1>List of Qualitifed Auditors </h1>';

      //Detail: B *****List of Qualitifed Auditors ****//.
      // $auditor_chart_path = drupal_get_path('module', 'aps_audit_report_analysis') . '/highchart-images-pdf/Auditor-Performance.jpeg';
      // $data_qualified_auditors = $this->getdatafromuri('auditor','/auditor-and-audit-export/'.$unit_reference.'?field_score_value_greater=5');
      // if(count($data_qualified_auditors)){
      //   $html .= '<table id = "qualified-auditors" style="width:100%;">
      //     <thead>
      //         <tr>
      //             <th>'.$this->t('Sl No.').'</th>
      //             <th>'.$this->t('Name: ').'</th>
      //             <th>'.$this->t('Score: ').'</th>
      //             <th>'.$this->t('Function: ').'</th>
      //         </tr>
      //     </thead>';
      //   $qualified_auditors_count = 0;
      //   foreach ($data_qualified_auditors as $data_qualified_auditors_key => $data_qualified_auditors_value) {
      //     $html .= '<tr>';
      //     $html .= '<td>' . $qualified_auditors_count . '</td>';
      //     $html .= '<td>' . $data_qualified_auditors_value['name'] . '</td>';
      //     $html .= '<td>' . $data_qualified_auditors_value['score'] . '</td>';
      //     $html .= '<td>' . $data_qualified_auditors_value['function'] . '</td>';
      //     $html .= '</tr>';
      //     $qualified_auditors_count++;
      //   }
      //   $html .= '</table>';
      // }
      // else{
      //   $html .= '<table id = "qualified-auditors" style="width:100%;">
      //     <thead>
      //         <tr>
      //             <th>'.$this->t('Sl No.').'</th>
      //             <th>'.$this->t('Name: ').'</th>
      //             <th>'.$this->t('Score: ').'</th>
      //             <th>'.$this->t('Function: ').'</th>
      //         </tr>
      //     </thead>';
      //   $html .= '<tfoot>
      //       <tr>
      //           <th></th>
      //           <th></th>
      //           <th><b>No content Found</b></th>
      //           <th></th>
      //       </tr>
      //   </tfoot><br><br>';

      // }
      // $html .= '</br>';
      // $html .= ' <img src='.$auditor_chart_path.' height="700" width="450"> ';
      // $html .= '<h1>List of Process/Product/Business Process </h1>';

      // //Detail: B *****List of Process ****//.
      // $data_process = $this->getdatafromuri('process','/get-registration-data/'.$unit_reference.'?type[]=assembly&type[]=manufacturing_process&type[]=customers_manual&type[]=customers_manual&type[]=business_process');
      // if(count($data_process)){
      //   $html .= '<table id = "qualified-auditors" style="width:100%;">
      //     <thead>
      //         <tr>
      //             <th>'.$this->t('Sl No.').'</th>
      //             <th>'.$this->t('Name: ').'</th>
      //             <th>'.$this->t('Type: ').'</th>
      //             <th>'.$this->t('Unit Name: ').'</th>
      //         </tr>
      //     </thead>';
      //   $process_count = 0;
      //   foreach ($data_process as $data_process_key => $data_process_value) {
      //     $html .= '<tr>';
      //     $html .= '<td>' . $process_count . '</td>';
      //     $html .= '<td>' . $data_process_value['title'] . '</td>';
      //     $html .= '<td>' . $data_process_value['type'] . '</td>';
      //     $html .= '<td>' . $data_process_value['unit_name'] . '</td>';
      //     $html .= '</tr>';
      //     $process_count++;
      //   }
      //   $html .= '</table>';
      // }
    }
    elseif($report_type == 'list_of_business_process'){
      $html .= '<h1>List of Business Process</h1>';
      $data_business_process = $this->getdatafromuri('business_process','/ncr-car-management-details/'.$unit_reference.'?type[]=business_process'.$date_range_query);
      if(count($data_business_process)){
        $html .= '<table id = "list-business-process" style="width:100%;">
          <thead>
              <tr>
                  <th>'.$this->t('Sl No.').'</th>
                  <th>'.$this->t('Title: ').'</th>
                  <th>'.$this->t('Business Head').'</th>
                  <th>'.$this->t('Business Process Effectiveness').'</th>
                  <th>'.$this->t('Business Process Efficiency').'</th>
              </tr>
          </thead>';
        $planned_count = 0;
        foreach ($data_business_process as $data_business_process_paragraph_generation_key => $data_business_process_paragraph_generation_val) {
          $html .= '<tr>';
          $html .= '<td>' . $planned_count . '</td>';
          $html .= '<td>' . $data_business_process_paragraph_generation_val['title'] . '</td>';
          $html .= '<td>' . $data_business_process_paragraph_generation_val['business_head'] . '</td>';

          $html .= '<td>' .'<b>Effectiveness: </b>'. $data_business_process_paragraph_generation_val['effectiveness'][0]->field_effectiveness.'<br><b>Target: </b>'.$data_business_process_paragraph_generation_val['effectiveness'][0]->field_target.'<br><b>UOM: </b>'.$data_business_process_paragraph_generation_val['effectiveness'][0]->field_uom_text . '</td>';

          $html .= '<td>' .'<b>Effieciency: </b>'. $data_business_process_paragraph_generation_val['efficiency'][0]->field_efficiency.'<br><b>Target: </b>'.$data_business_process_paragraph_generation_val['efficiency'][0]->field_target.'<br><b>UOM: </b>'.$data_business_process_paragraph_generation_val['efficiency'][0]->field_uom_text. '</td>';
          $html .= '</tr>';
          $planned_count++;
        }

        $html .= '</table>';
      }
    }
    elseif($report_type == 'list_of_process'){
      $html .= '<h1>List of Processes</h1>';
      $list_of_process = $this->getdatafromuri('process','/ncr-car-management-details/'.$unit_reference.'?type[]=manufacturing_process&type[]=assembly'.$date_range_query);
      if(count($list_of_process)){
        $html .= '<table id = "list-business-process" style="width:100%;">
            <thead>
                <tr>
                    <th>'.$this->t('Sl No.').'</th>
                    <th>'.$this->t('Title: ').'</th>
                    <th>'.$this->t('Process Type').'</th>
                    <th>'.$this->t('Shift').'</th>
                    <th>'.$this->t('Section').'</th>
                </tr>
            </thead>';
          $planned_count = 0;
          foreach ($list_of_process as $list_of_process_key => $list_of_process_val) {
            $html .= '<tr>';
            $html .= '<td>' . $planned_count . '</td>';
            $html .= '<td>' . $list_of_process_val['title'] . '</td>';
            $html .= '<td>' . $list_of_process_val['type'] . '</td>';
            $html .= '<td>' . $list_of_process_val['field_shift'] . '</td>';
            $html .= '<td>' . $list_of_process_val['field_select_section'] . '</td>';
            $html .= '</tr>';
            $planned_count++;
          }
        $html .= '</table>';
      }
    }
    elseif($report_type == 'list_of_product'){
      $html .= '<h1>List of Products</h1>';
      $list_of_product = $this->getdatafromuri('product','/ncr-car-management-details/'.$unit_reference.'?type[]=customers_manual'.$date_range_query);
      if(count($list_of_product)){
        $html .= '<table id = "list-business-process" style="width:100%;">
            <thead>
                <tr>
                    <th>'.$this->t('Sl No.').'</th>
                    <th>'.$this->t('Title: ').'</th>
                    <th>'.$this->t('Document').'</th>
                    <th>'.$this->t('QAM').'</th>
                    <th>'.$this->t('Version Date').'</th>
                    <th>'.$this->t('Version Level').'</th>
                </tr>
            </thead>';
          $planned_count = 0;
          foreach ($list_of_product as $list_of_product_key => $list_of_product_val) {
            $html .= '<tr>';
            $html .= '<td>' . $planned_count . '</td>';
            $html .= '<td>' . $list_of_product_val['title'] . '</td>';
            $html .= '<td>' . $list_of_product_val['field_document'] . '</td>';
            $html .= '<td>' . $list_of_product_val['field_qam'] . '</td>';
            $html .= '<td>' . $list_of_product_val['field_version_date'] . '</td>';
            $html .= '<td>' . $list_of_product_val['field_version_level'] . '</td>';
            $html .= '</tr>';
            $planned_count++;
          }
        $html .= '</table>';
      }
    }
    elseif($report_type == 'list_of_qualified_auditors'){
      $this->getAuditorGraph($unit_reference);
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
    }
    elseif($report_type == 'audit_check_list'){
      $audit_check_list = $this->getdatafromuri('checklist','/ncr-car-management-details/'.$unit_reference.'?type[]=planned_events'.$date_range_query);
      if(count($audit_check_list)){
        $html .= '<table id = "list-business-process" style="width:100%;">
            <thead>
                <tr>
                    <th>'.$this->t('Sl No.').'</th>
                    <th>'.$this->t('Checklist Number ').'</th>
                    <th>'.$this->t('Checklist Title ').'</th>
                    <th>'.$this->t('Question: ').'</th>
                    <th>'.$this->t('Options').'</th>
                    <th>'.$this->t('Answered').'</th>
                    <th>'.$this->t('Finding Categories').'</th>
                    <th>'.$this->t('Clause No.').'</th>
                    <th>'.$this->t('KPI').'</th>
                </tr>
            </thead>';
          $planned_count = 0;
          foreach ($audit_check_list as $audit_check_list_key => $audit_check_list_val) {
            $html .= '<tr>';
            $html .= '<td>' . $planned_count . '</td>';
            $html .= '<td>' . $audit_check_list_val['sno'] . '</td>';
            $html .= '<td>' . $audit_check_list_val['title'] . '</td>';
            $html .= '<td>' . $audit_check_list_val['question'] . '</td>';
            $html .= '<td>' . $audit_check_list_val['answers'] . '</td>';
            $html .= '<td>' . $audit_check_list_val['default_checked'] . '</td>';
            $html .= '<td>' . $audit_check_list_val['field_finding_categories'] . '</td>';
            $html .= '<td>' . $audit_check_list_val['clause_no'] . '</td>';
            $html .= '<td>' . strtoupper($audit_check_list_val['kpi']) . '</td>';
            $html .= '</tr>';
            $planned_count++;
          }
        $html .= '</table>';
      }
    }
    elseif($report_type == 'department_wise_audit_plan'){
      $department_wise_audit_plan = $this->getdatafromuri('dept_wise_audit','/ncr-car-management-details/'.$unit_reference.'?type[]=planned_events'.$date_range_query);
      if(count($department_wise_audit_plan)){
          foreach ($department_wise_audit_plan as $department_wise_audit_plan_key => $department_wise_audit_plan_val) {
            $html .= '<table id = "list-business-process" style="width:100%;">
            <thead>
                <tr>
                  <th>
                    <h2>Department Name: '.Node::load($department_wise_audit_plan_key)->title->value.'<h2/>
                  </th>
                </tr>
                <tr>
                    <th>'.$this->t('Sl No.').'</th>
                    <th>'.$this->t('Title').'</th>
                    <th>'.$this->t('Section Name ').'</th>
                    <th>'.$this->t('Start Date').'</th>
                    <th>'.$this->t('End Date').'</th>
                    <th>'.$this->t('Shift').'</th>
                    <th>'.$this->t('Auditor').'</th>
                    <th>'.$this->t('Auditee.').'</th>
                    <th>'.$this->t('Current State').'</th>
                </tr>
            </thead>';
            $planned_count = 1;
            foreach ($department_wise_audit_plan_val as $i => $j) {
              $html .= '<tr>';
              $html .= '<td>' . $planned_count . '</td>';
              $html .= '<td>' . $j['title'] . '</td>';
              $html .= '<td>' . $j['field_section'] . '</td>';
              $html .= '<td>' . $j['field_start_date'] . '</td>';
              $html .= '<td>' . $j['field_end_date'] . '</td>';
              $html .= '<td>' . $j['field_shift'] . '</td>';
              $html .= '<td>' . $j['field_auditor'] . '</td>';
              $html .= '<td>' . $j['field_select_auditee'] . '</td>';
              $html .= '<td>' . $j['moderation_state'] . '</td>';
              $html .= '</tr>';
              $planned_count++;
            }
          }
        $html .= '</table>';
      }
    }
    elseif($report_type == 'a_matrix_for_auditor_score_and_each_realted_function'){
      $data_auditor_function = $this->getdatafromuri('auditor_functions','/auditor-and-audit-export/'.$unit_reference.'?field_score_value_greater=0');
      if(count($data_auditor_function)){
          foreach ($data_auditor_function as $data_auditor_function_key => $data_auditor_function_val) {
            $html .= '<table id = "list-business-process" style="width:100%;">
            <thead>
                <tr>
                  <th>
                    <h2>Auditor Name: '.User::load($data_auditor_function_key)->name->value.'<h2/>
                  </th>
                </tr>
                <tr>
                    <th>'.$this->t('Sl No.').'</th>
                    <th>'.$this->t('Auditor Name').'</th>
                    <th>'.$this->t('Function').'</th>
                    <th>'.$this->t('Score').'</th>
                    <th>'.$this->t('Audit Conducted').'</th>
                </tr>
            </thead>';
            $planned_count = 1;
            foreach ($data_auditor_function_val as $i => $j) {
              $html .= '<tr>';
              $html .= '<td>' . $planned_count . '</td>';
              $html .= '<td>' . $j['name'] . '</td>';
              $html .= '<td>' . $j['field_functions_qualified'] . '</td>';
              $html .= '<td>' . $j['field_score'] . '</td>';
              $html .= '<td>' . $j['title'] . '</td>';
              $html .= '</tr>';
              $planned_count++;
            }
          }
        $html .= '</table>';
      }
    }
    elseif($report_type == 'matrix_depicting_business_process_and_departments'){
      $data_bp_dept = $this->getdatafromuri('bp_dept','/ncr-car-management-details/'.$unit_reference.'?type[]=business_process'.$date_range_query);
      if(count($data_bp_dept)){
          foreach ($data_bp_dept as $data_bp_dept_key => $data_bp_dept_value) {
            $html .= '<table id = "list-business-process" style="width:100%;">
            <thead>
                <tr>
                  <th>
                    <h2>Department Name: '.Node::load($data_bp_dept_key)->title->value.'<h2/>
                  </th>
                </tr>
                <tr>
                  <th>'.$this->t('Sl No.').'</th>
                  <th>'.$this->t('Business Process: ').'</th>
                  <th>'.$this->t('Department: ').'</th>
                  <th>'.$this->t('Business Head').'</th>
                  <th>'.$this->t('Business Process Effectiveness').'</th>
                  <th>'.$this->t('Business Process Efficiency').'</th>
                </tr>
            </thead>';
            $planned_count = 1;
            foreach ($data_bp_dept_value as $i => $j) {
              $html .= '<tr>';
              $html .= '<td>' . $planned_count . '</td>';
              $html .= '<td>' . $j['business_title'] . '</td>';
              $html .= '<td>' . $j['department_title'] . '</td>';
              $html .= '<td>' . $j['business_head'] . '</td>';

              $html .= '<td>' .'<b>Effectiveness: </b>'. $j['effectiveness'][0]->field_effectiveness.'<br><b>Target: </b>'.$j['effectiveness'][0]->field_target.'<br><b>UOM: </b>'.$j['effectiveness'][0]->field_uom_text . '</td>';

              $html .= '<td>' .'<b>Effieciency: </b>'. $j['efficiency'][0]->field_efficiency.'<br><b>Target: </b>'.$j['efficiency'][0]->field_target.'<br><b>UOM: </b>'.$j['efficiency'][0]->field_uom_text. '</td>';
              $html .= '</tr>';
              $planned_count++;
            }
          }
        $html .= '</table>';
      }
    }
    elseif($report_type == 'ncr'){
      $checklist_car = $this->getdatafromuri('checklist_car','/ncr-car-management-details/'.$unit_reference.'?type[]=auditor_report'.$date_range_query);
      if(count($checklist_car)){
        foreach ($checklist_car as $checklist_car_key => $checklist_car_key_val) {
          $html .= '<table id = "list-business-process" style="width:100%;">
              <thead>
                <tr>
                  <th>
                    <h2>Event Name: '.Node::load($checklist_car_key)->title->value.'<h2/>
                  </th>
                </tr>
                  <tr>
                      <th>'.$this->t('Sl No.').'</th>
                      <th>'.$this->t('Event Name').'</th>
                      <th>'.$this->t('Occurence ').'</th>
                      <th>'.$this->t('Standards ').'</th>
                      <th>'.$this->t('Question: ').'</th>
                      <th>'.$this->t('Answered').'</th>
                      <th>'.$this->t('Interim Action').'</th>
                      <th>'.$this->t('Corrective Action').'</th>
                  </tr>
              </thead>';
            $planned_count = 1;
            foreach ($checklist_car_key_val as $j) {
              $html .= '<tr>';
              $html .= '<td>' . $planned_count . '</td>';
              $html .= '<td>' . $j['event_title'] . '</td>';
              $html .= '<td>' . $j['field_occurence'] . '</td>';
              $html .= '<td>' . $j['field_standards'] . '</td>';
              $html .= '<td>' . $j['field_question'] . '</td>';
              $html .= '<td>' . $j['field_result'] . '</td>';
              $html .= '<td>' . $j['interim'] . '</td>';
              $html .= '<td>' . $j['corrective'] . '</td>';
              $html .= '</tr>';
              $planned_count++;
            }
          $html .= '</table>';
        }
      }
    }
    elseif($report_type == 'time_line_master'){
      $timeline = $this->getTimelinemaster($entity_audit_cycle);
      if(count($timeline)){
        $html .= '
          <div id="data-view">
          <h2>Audit Cycle Schedule Release</h1>
          <label class="label-name">'.$this->t('<h3>Audit Frequency</h3>').'</label><span class="info-value">'.$timeline['frequency'].'</span>
          <div class="col-md-12 row schedule-release-contract-info">
            <div class="schedule-release-data-list-a col-md-6">
              <label class="label-name">'.$this->t('<h3>Annual Calendar</h3>').'</label><span class="info-value">'.$timeline['annual_calendar'].'</span>
              </br>
              <label class="label-name">'.$this->t('<h3>Department</h3>').'</label><span class="info-value">'.$timeline['department'].'</span>

              <label class="label-name">'.$this->t('<h3>Conclusion of Pre- Audit execution confirmation </h3>').'</label><span class="info-value">'.$timeline['pre_audit_execution'].'</span>

              <label class="label-name">'.$this->t('<h3>Conclusion of Audit execution confirmation </h3>').'</label><span class="info-value">'.$timeline['audit_excution'].'</span>


            <div class="schedule-release-list-b col-md-6">
              <label class="label-name">'.$this->t('<h3>CAR release by Auidtee </h3>').'</label><span class="info-value">'.$timeline['car_release'].'</span>
              </br>
              <label class="label-name">'.$this->t('<h3>Rescheduling of dates </h3>').'</label><span class="info-value">'.$timeline['reschedule'].'</span>

              <label class="label-name">'.$this->t('<h3>Unplanned audit schedule </h3>').'</label><span class="info-value">'.$timeline['unplanned_audit'].'</span>

              <label class="label-name">'.$this->t('<h3>Non conformity release </h3>').'</label><span class="info-value">'.$timeline['nc_release'].'</span>

            </div>
            </div>
          </div>       
          ';
      }
    }
    elseif($report_type == 'risk_report'){
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
      $i = 0;
      foreach ($data as $key => $value) {
        if($type == 'event'){
          $options[$value->nid]['title'] = $value->title;
          $options[$value->nid]['start_date']  = $value->field_start_date;
          $options[$value->nid]['end_date']  = $value->field_end_date;
          $options[$value->nid]['field_audit_type']  = $value->field_audit_type;
          $options[$value->nid]['field_auditor']  = $value->field_auditor;
          $options[$value->nid]['field_select_auditee']  = $value->field_select_auditee;
        }
        elseif($type == 'auditor'){
          $options[$value->uid]['name'] = $value->name;
          $options[$value->uid]['score']  = $value->field_score;
          $options[$value->uid]['function']  = $value->field_functions_qualified;
        }
        elseif($type == 'business_process'){
          $node_business_process = Node::load($value->nid);
          $field_business_process_effective = $node_business_process->field_business_process_effective->target_id;
          $field_business_process_efficienc = $node_business_process->field_business_process_efficienc->target_id;
          $options[$value->nid]['title'] = $node_business_process->title->value;
          $options[$value->nid]['business_head'] = $node_business_process->field_business_head_name->value;
          $data_effectiveness = $this->getJSONFromExport('/paragraph-export/'.$field_business_process_effective);
          $data_efficiency = $this->getJSONFromExport('/paragraph-export/'.$field_business_process_efficienc);
          $options[$value->nid]['effectiveness'] = $data_effectiveness;
          $options[$value->nid]['efficiency'] = $data_efficiency;
        }
        elseif($type == 'process'){
          $options[$value->nid]['title'] = $value->title;
          $options[$value->nid]['type'] = $value->type;
          $options[$value->nid]['field_shift'] = $value->field_shift;
          $options[$value->nid]['field_select_section'] = $value->field_select_section;
          $options[$value->nid]['title'] = $value->title;
        }
        elseif($type == 'product'){
          if($value->field_document != ''){
            $fid = (int)$value->field_document;
            $file = \Drupal\file\Entity\File::load($fid);
            $path = file_create_url($file->getFileUri());
          }
          else{
            $path = ' - ';
          }
          $options[$value->nid]['title'] = $value->title;
          $options[$value->nid]['field_document'] = $path != ' - ' ? '<a href='.$path.'>DOCUMENT</a>' : ' - ';
          $options[$value->nid]['field_qam'] = $value->field_qam ? '<a href='.$value->field_qam.'>QAM</a>' : ' - ';
          $options[$value->nid]['field_version_date'] = $value->field_version_date ? $value->field_version_date : ' - ';
          $options[$value->nid]['field_version_level'] = $value->field_version_level ? $value->field_version_level : ' - ';
        }
        elseif($type == 'checklist'){
          $load_checklist = UpdateAuditFindings::getAuditDetails($value->field_checklist);
          $checklist_title = Node::load($value->field_checklist)->title->value;
          foreach ($load_checklist as $checklist_key => $checklist_value) {
            $options[$checklist_key]['title'] = $checklist_title;
            $options[$checklist_key]['sno'] = $checklist_value['sno'];
            $options[$checklist_key]['kpi'] = $checklist_value['kpi'];
            $options[$checklist_key]['question'] = $checklist_value['question'];
            $options[$checklist_key]['answers'] = '
                  <b>Optimised:<b>'.$checklist_value['answers']['Optimised']['answer'].'<br>
                  <b>Qualified:<b>'.$checklist_value['answers']['Qualified']['answer'].'<br>
                  <b>Effecient:<b>'.$checklist_value['answers']['Effecient']['answer'].'<br>
                  <b>Poor:<b>'.$checklist_value['answers']['Poor']['answer'].'<br>'
                  ;
            $options[$checklist_key]['field_answer_type'] = $checklist_value['field_answer_type'];
            $options[$checklist_key]['default_checked'] = $checklist_value['default_checked'];
            if(count($checklist_value['field_finding_categories']) > 0){
              foreach ($checklist_value['field_finding_categories'] as $finding => $val) {
                if($term = Term::load($val['target_id'])){
                  $name = $term->getName().'<br>';
                }
                else{
                  $name = 'Not Found.';
                }
              }
            }
            else{
              $name = 'Not Found.';
            }
            $options[$checklist_key]['field_finding_categories'] = $name;
            if($checklist_value['clause_no'] != ''){
              $clause_no = Node::load($checklist_value['clause_no'])->title->value;
            }
            else{
              $clause_no = 'Not Found';
            }
            $options[$checklist_key]['clause_no'] = $clause_no;
          }
        }
        elseif($type == 'dept_wise_audit'){
          $options[$value->department_nid][$value->nid]['title'] = $value->title;
          $options[$value->department_nid][$value->nid]['field_department'] = $value->field_department;
          $options[$value->department_nid][$value->nid]['field_section'] = $value->field_section;
          $options[$value->department_nid][$value->nid]['field_start_date'] = $value->field_start_date;
          $options[$value->department_nid][$value->nid]['field_end_date'] = $value->field_end_date;
          $options[$value->department_nid][$value->nid]['field_shift'] = $value->field_shift;
          $options[$value->department_nid][$value->nid]['field_auditor'] = $value->field_auditor;
          $options[$value->department_nid][$value->nid]['field_select_auditee'] = $value->field_select_auditee;
          $options[$value->department_nid][$value->nid]['moderation_state'] = $value->moderation_state;
        }
        elseif($type == 'auditor_functions'){
          $options[$value->uid][$value->field_functions_qualified]['name'] = $value->name;
          $options[$value->uid][$value->field_functions_qualified]['field_functions_qualified'] = $value->field_functions_qualified;
          $options[$value->uid][$value->field_functions_qualified]['field_score'] = $value->field_score;
          $options[$value->uid][$value->field_functions_qualified]['title'] = $value->title;
        }
        elseif($type == 'bp_dept'){
          $query = \Drupal::database()->select('node_field_data', 'n');
          $query->join('node__field_select_business_process', 'bp', 'n.nid = bp.field_select_business_process_target_id');
          $query->fields('bp',['entity_id', 'bundle']);
          $query->condition('bp.bundle', 'department');
          $query->condition('bp.field_select_business_process_target_id', $value->nid);
          $nids = $query->execute()->fetchAll();
          $node_business_process = Node::load($value->nid);
          $field_business_process_effective = $node_business_process->field_business_process_effective->target_id;
          $field_business_process_efficienc = $node_business_process->field_business_process_efficienc->target_id;
          $data_effectiveness = $this->getJSONFromExport('/paragraph-export/'.$field_business_process_effective);
          $data_efficiency = $this->getJSONFromExport('/paragraph-export/'.$field_business_process_efficienc);

          foreach ($nids as $dept => $dept_value) {
            $options[$dept_value->entity_id][$value->nid]['department_title'] = Node::load($dept_value->entity_id)->title->value;
            $options[$dept_value->entity_id][$value->nid]['business_title'] = $node_business_process->title->value;
            $options[$dept_value->entity_id][$value->nid]['business_head'] = $node_business_process->field_business_head_name->value;
            $options[$dept_value->entity_id][$value->nid]['effectiveness'] = $data_effectiveness;
            $options[$dept_value->entity_id][$value->nid]['efficiency'] = $data_efficiency;
          }
        }
        elseif($type == 'checklist_car'){
          $node_business_process = Node::load($value->nid);
          $audit_list_id = $node_business_process->field_audit_list->target_id;
          $audit_list_data = Paragraph::load($audit_list_id)->toArray();
          $interim_action = $audit_list_data['field_interim_action'];
          $corrective_action = $audit_list_data['field_corrective_action'];
          $array_action =array_merge($interim_action,$corrective_action);

          $query = \Drupal::database()->select('node_field_data', 'nf');
          $query->join('node__field_refere', 'n', 'nf.nid = n.field_refere_target_id');
          $query->fields('n',['entity_id', 'bundle', 'field_refere_target_id']);
          $query->condition('nf.type', 'planned_events');
          $query->condition('n.entity_id', $value->nid);
          $nids = $query->execute()->fetchAll();
          foreach ($array_action as $action_key => $action_value) {
            $load = Paragraph::load($action_value['target_id']);
            if($load->type->target_id == 'interrim_action'){
              $interim .= 'Intrim Action: '.$load->field_description_poor->value.'<br>';
            }
            elseif ($load->type->target_id == 'corrective_action') {
              $corrective .= 'Corrective Action: '.$load->field_description_answer->value.'<br>';
            }
          }

          foreach ($nids as $event => $event_value) {
            $options[$event_value->field_refere_target_id][$value->nid]['event_title'] = Node::load($event_value->entity_id)->title->value;
            $options[$event_value->field_refere_target_id][$value->nid]['field_occurence'] = $value->field_occurence;
            $options[$event_value->field_refere_target_id][$value->nid]['field_standards'] =  $value->field_standards;
            $options[$event_value->field_refere_target_id][$value->nid]['field_status_1'] =  $value->field_status_1;
            $options[$event_value->field_refere_target_id][$value->nid]['field_question'] =  $value->field_question;
            $options[$event_value->field_refere_target_id][$value->nid]['field_result'] =  $value->field_result;
            $options[$event_value->field_refere_target_id][$value->nid]['interim'] =  $interim;
            $options[$event_value->field_refere_target_id][$value->nid]['corrective'] =  $corrective;
          }
        }
        elseif($type == 'timeline_master'){
          $options[$value->uid][$value->field_functions_qualified]['name'] = $value->name;
          $options[$value->uid][$value->field_functions_qualified]['field_functions_qualified'] = $value->field_functions_qualified;
          $options[$value->uid][$value->field_functions_qualified]['field_score'] = $value->field_score;
          $options[$value->uid][$value->field_functions_qualified]['title'] = $value->title;
        }
      }
    }

    return $options;
  }

  public function getJSONFromExport($url){
    global $base_url;
    $client = \Drupal::httpClient();
    $request = $client->get($base_url.$url);
    $response = $request->getBody();
    $data = json_decode($response);
    return $data;
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

  public function getTimelinemaster($data){
    $scheduled_release = Paragraph::load($data->field_schedule_release->target_id); 
    $options['id'] = $data->id->value;
    $options['frequency'] = $data->field_audit_frequency->value;
    $options['car_release'] = $data->field_car_release_by_auidtee_->value;
    $options['nc_release'] = $data->field_non_conformity_release_->value;
    $options['reschedule'] = $data->field_rescheduling_of_dates_->value;
    $options['annual_calendar'] = $scheduled_release->field_annual_calendar->value;
    $options['audit_excution'] = $scheduled_release->field_conclusion_of_audit_execut->value;
    $options['pre_audit_execution'] = $scheduled_release->field_conclusion_of_pre_audit_ex->value;
    $options['department'] = $scheduled_release->field_department->value;
    $options['unplanned_audit'] = $scheduled_release->field_unplanned_audit_schedule_->value;

    return $options;
  }
}
