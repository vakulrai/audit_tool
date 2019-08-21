<?php

namespace Drupal\aps_general\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("ncr_status")
 */
class NcrStatus extends FieldPluginBase {

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
    $paragraph_object  = $values->_entity;
    $options = [
      '_none' => '-None-',
      'implemented' => 'Implemented',
      'not-implemented' => 'Not Implemented',
    ];

    if($paragraph_object->id()){
      $form['ncr_check'] = [
        '#type' => 'select',
        '#required' => TRUE,
        '#title' => t('<b>NCR Status</b> :'),
        '#options' => $options,
        '#attributes' => ['id' => 'ncr-details', 'record_reference' => $paragraph_object->id()],
        '#value'=> $paragraph_object->field_car_status->value,
      ];
    }
    return $form;
  }

}
