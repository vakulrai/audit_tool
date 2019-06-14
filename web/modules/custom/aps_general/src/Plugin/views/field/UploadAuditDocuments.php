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
 * @ViewsField("upload_audit_documents")
 */
class UploadAuditDocuments extends FieldPluginBase {

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
    $config = \Drupal::service('config.factory')->getEditable('aps_general.adduserroles');
    $current_count = $config->get('current_count');
    $form['doc'] = [
        '#type' => 'link',
        '#title' => t('Upload'),
        '#url' => Url::fromRoute('aps_pre_audit.get_info',['id' => $node->id()]),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'button',
          ],
        ],
      ];
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

}
