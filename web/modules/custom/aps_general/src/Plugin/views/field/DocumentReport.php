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
 * @ViewsField("pre_audit_report")
 */
class DocumentReport extends FieldPluginBase {

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
    if($user_role === 'auditor'){
      if($node->bundle() == 'pre_audit_records'){
        if($node->get('field_verified')->value == 1){
          $report = 1;
        }else{
          $report = 0;
        }
        if($report == 0){
          $form['report'] = [
            '#type' => 'link',
            '#title' => t('Report'),
            '#url' => Url::fromRoute('aps_general.create_reports',['id' => $node->id()]),
            '#attributes' => [
              'class' => [
                'use-ajax',
                'button',
              ],
            ],
          ];
        }
      }
      elseif ($node->bundle() == 'planned_events') {
        $event_start_date_timestamp = $node->get('field_start_date')->value;
        $current_date = new \DateTime();
        $date_of_audit = new \DateTime(date('Y-m-d H:i:s', $event_start_date_timestamp));
        $audit_date_current_date_diff = $current_date->diff($date_of_audit);
        $current_date_invert = $audit_date_current_date_diff->invert;
        $total_hours_to_audit = $audit_date_current_date_diff->days * 24 + $audit_date_current_date_diff->h;
        if ($total_hours_to_audit > 0  && $current_date_invert != 1) {
          $form['report'] = [
            '#type' => 'link',
            '#title' => t('Report/Execute'),
            '#url' => Url::fromRoute('aps_general.create_reports',['id' => $node->id()]),
            '#attributes' => [
              'class' => [
                'use-ajax',
                'button',
              ],
            ],
          ];
        }
      }
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    }
    
    return $form;
  }

}
