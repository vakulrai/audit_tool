<?php

namespace Drupal\aps_general\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\aps_general\Form\CsvEntityImport;

/**
 * Provides a 'CsvImportBlock' block.
 *
 * @Block(
 *  id = "csv_import_block",
 *  admin_label = @Translation("Csv import block"),
 * )
 */
class CsvImportBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $form = \Drupal::formBuilder()->getForm(CsvEntityImport::class);
  }

}
