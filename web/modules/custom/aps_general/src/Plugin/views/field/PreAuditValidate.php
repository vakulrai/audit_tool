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
    $select_audit = $node->get('field_select_audit')->target_id;
    $event_start_date_timestamp = $node->get('field_start_date')->value;
    $diff = $event_start_date_timestamp - time();
    $days = floor($diff/(60*60*24));
    $hours = round(($diff-$days*60*60*24)/(60*60));
    $time_remaining = $days .' Days '. $hours . ' Hour';
    if ($days > 0) {
      $form['add_delta_qa'] = [
        '#type' => 'link',
        '#title' => t('Pre Audit'),
        '#url' => Url::fromUserInput('/preaudit/'.$node->id().'?ref='.$select_audit),
        ];
    }
    return $form;
  }

}
