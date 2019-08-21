<?php

namespace Drupal\aps_general\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Random;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("delta_qa")
 */
class AddDeltaQuestion extends FieldPluginBase {

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
    $nid = \Drupal::request()->query->get('ref');
    $current_uri_destination = trim(\Drupal::request()->getRequestUri(), '/');
    $title = Markup::create('<span data-toggle="tooltip" data-placement="top" title="Auditor expert comments/view"><h1>dQ</h1></span>');
    $form['add_delta_qa'] = [
      '#type' => 'link',
      '#title' => $title,
      '#url' => Url::fromRoute('node.add',['node_type' => 'answers', 'destination' => $current_uri_destination, 'checklist_type' => 'delta']),
    ];
    return $form;
  }

}
