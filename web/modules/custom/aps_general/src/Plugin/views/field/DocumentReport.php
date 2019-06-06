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
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

}
