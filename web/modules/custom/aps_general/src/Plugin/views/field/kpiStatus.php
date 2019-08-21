<?php

namespace Drupal\aps_general\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("kpi_status")
 */
class kpiStatus extends FieldPluginBase {

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
    $nid  = $values->_entity;
    $node_object  = Node::load($nid->id());
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    foreach ($roles as $key => $value) {
      $user_role = $value;
    }

    $options = [
      '_none' => '-None-',
      'achieved' => 'Achieved',
      'not-achieved' => 'Not Achieved',
    ];
    
    if($user_role == 'auditor'){
      $disable_field = FALSE;
    }
    else{
      $disable_field = TRUE;
    }

    if(isset($node_object->field_kpi_status->value)){
      $form['kpi_check'] = [
        '#type' => 'select',
        '#title' => t('<b>KPI Status</b> :'),
        '#options' => $options,
        '#attributes' => ['id' => 'kpi-details', 'record_reference' => $nid->id()],
        '#value'=> $node_object->field_kpi_status->value,
        '#disabled' => $disable_field,
      ];
    }
    else{
       $form['kpi_check'] = [
        '#type' => 'select',
        '#title' => t('<b>KPI Status</b> :'),
        '#options' => $options,
        '#attributes' => ['id' => 'kpi-details', 'record_reference' => $nid->id()],
        '#disabled' => $disable_field,
      ];
    }
    return $form;
  }

}
