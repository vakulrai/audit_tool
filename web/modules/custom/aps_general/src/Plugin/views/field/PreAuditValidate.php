<?php

namespace Drupal\aps_general\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("remainingday_event_validate")
 */
class PreAuditValidate extends FieldPluginBase {

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
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    foreach ($roles as $key => $value) {
      $user_role = $value;
    }
    $select_audit = $node->get('field_select_audit')->target_id;
    $event_start_date_timestamp = $node->get('field_start_date')->value;
    if($node->get('field_audit_type')->value === 'internal'){
      $internal_audit_type = $node->get('field_internal_audit_type')->value;
      switch ($internal_audit_type) {
        case 'systems':
          $audit_reference =  $node->get('field_list_of_systems')->target_id;
          $query = \Drupal::database()->select('node__field_list_of_systems', 'cm');
          $query->fields('cm',['field_list_of_systems_target_id', 'entity_id']);
          $query->condition('cm.bundle', 'internal_audit');
          $query->condition('cm.field_list_of_systems_target_id', $audit_reference);
          $nids = $query->execute()->fetchAll();
          $ia_ref = $nids[0]->entity_id;
          break;

        case 'process':
          $audit_reference =  $node->get('field_list_of_process')->target_id;
          $query = \Drupal::database()->select('node__field_list_of_process', 'cm');
          $query->fields('cm',['field_list_of_process_target_id', 'entity_id']);
          $query->condition('cm.bundle', 'internal_audit');
          $query->condition('cm.field_list_of_process_target_id', $audit_reference);
          $nids = $query->execute()->fetchAll();
          $ia_ref = $nids[0]->entity_id;
          break;

        case 'product':
          $audit_reference =  $node->get('field_list_of_product')->target_id;
          $query = \Drupal::database()->select('node__field_list_of_product', 'cm');
          $query->fields('cm',['field_list_of_product_target_id', 'entity_id']);
          $query->condition('cm.bundle', 'internal_audit');
          $query->condition('cm.field_list_of_product_target_id', $audit_reference);
          $nids = $query->execute()->fetchAll();
          $ia_ref = $nids[0]->entity_id;
          break;
        
        default:
          # code...
          break;
      }
    }

    $diff = $event_start_date_timestamp - time();
    $days = floor($diff/(60*60*24));
    $hours = round(($diff-$days*60*60*24)/(60*60));
    $time_remaining = $days .' Days '. $hours . ' Hour';
    if ($days > 0) {
      if($user_role == 'auditor'){
        $form['add_delta_qa'] = [
          '#type' => 'link',
          '#title' => t('Pre Audit'),
          '#url' => Url::fromUserInput('/preaudit/'.$node->id().'?ref='.$ia_ref),
        ];
      }
      elseif ($user_role == 'auditee') {
        $form['add_delta_qa'] = [
          '#type' => 'link',
          '#title' => t('UPLOAD'),
          '#url' => Url::fromUserInput('/documentrecords/'.$node->id()),
        ];
      }
    }
    return $form;
  }

}
