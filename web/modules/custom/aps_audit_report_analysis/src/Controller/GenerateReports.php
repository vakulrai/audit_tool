<?php

namespace Drupal\aps_audit_report_analysis\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use Drupal\aps_audit_report_analysis\Plugin\Block\RiskManagement;


/**
 * Controller routines for aps_audit_report_analysis routes.
 */
class GenerateReports extends ControllerBase {

  public function generateHTMLReports($report_type) {
    $output = $this->getDataforHTML($report_type);
    $options = new Options();
    $options->set('isRemoteEnabled', TRUE);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream();
    $file_name = "Consumption-Summary";
    $dompdf->get_canvas()->page_text(370, 570, "Page: {PAGE_NUM} of {PAGE_COUNT}", null, 12, array(0,0,0));
    $dompdf->stream($file_name,["Attachment" => 1]);
    return $dompdf;
  }

  public function getDataforHTML($report_type){
    global $base_url;
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $query = \Drupal::database()->select('audit_cycle__field_unit_reference', 'h');
    $query->fields('h',['entity_id']);
    $query->condition('h.field_unit_reference_target_id', 283);
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
      $html .= '<h1>Annual calendar plan</h1>';
      //Detail: A *****Get Annual calendar plan****//.
      $data_planned_event = $this->getdatafromuri('event','/ncr-car-management-details/283?type=planned_events');
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
      $data_qualified_auditors = $this->getdatafromuri('auditor','/auditor-and-audit-export/283?field_score_value_greater=5');
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
      $html .= '<h1>List of Process/Product/Business Process </h1>';

      //Detail: B *****List of Process ****//.
      $data_process = $this->getdatafromuri('process','/get-registration-data/283?type[]=assembly&type[]=manufacturing_process&type[]=customers_manual&type[]=customers_manual&type[]=business_process');
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
      $html .="</html>";
    }
    elseif($report_type == 'risk'){
      $risk_data = RiskManagement::build();
      $block_manager = \Drupal::service('plugin.manager.block');
      $config = [];
      $plugin_block = $block_manager->createInstance('risk_management', $config);
      $access_result = $plugin_block->access(\Drupal::currentUser());
      if (is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result) {
        return [];
      }
      $html = $plugin_block->build();
    }
    return $html;
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
}
