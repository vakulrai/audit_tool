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
    return $form;
  }

}
