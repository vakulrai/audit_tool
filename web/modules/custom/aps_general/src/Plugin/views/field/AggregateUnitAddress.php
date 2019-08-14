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
 * @ViewsField("aggregate_unit_address")
 */
class AggregateUnitAddress extends FieldPluginBase {

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
    $node = $values->_entity;
    if(count($node->field_remote_address->getValue()) > 0){
      foreach ($node->field_remote_address->getValue() as $key => $value) {
        $support_locations[] = $value['locality'];
      }
	    $field_address[] = $node->field_address->locality;
	    $options = array_merge($field_address , $support_locations);
	    foreach ($options as $adrss_key => $adrss_value) {
	      $combined_address .= '<b>'.$adrss_value.'</b><br>';
	    }	
    }

    $form['unit_address'] = [
      '#type' => 'markup',
      '#markup' => $combined_address,
    ];
    return $form;
  }

}
