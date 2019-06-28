<?php

namespace Drupal\aps_pre_audit\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\aps_pre_audit\Form\PreAuditForm;

/**
 * Provides a 'PreAuditBlock' block.
 *
 * @Block(
 *  id = "pre_audit_block",
 *  admin_label = @Translation("Pre audit block"),
 * )
 */
class PreAuditBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm(PreAuditForm::class);
    $reference_id = \Drupal::request()->query->get('ref');
    $data = PreAuditForm::getAuditDetails($reference_id);
    foreach ($data as $key => $value) {
      $data_qa[$key] = $value['sno'];
    }
    $build['#form'] = $form;
    $build['#data'] = $data_qa;
    $build['#theme'] = 'pre_audit_block';
    return $build;
  }

}
