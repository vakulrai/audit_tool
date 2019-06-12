<?php

namespace Drupal\aps_pre_audit\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a 'AskMoreInformationBlock' block.
 *
 * @Block(
 *  id = "ask_more_information_block",
 *  admin_label = @Translation("Ask more information block"),
 * )
 */
class AskMoreInformationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['info'] = [
        '#type' => 'link',
        '#title' => t('Ask For More Information'),
        '#url' => Url::fromRoute('aps_pre_audit.get_info'),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'button',
          ],
        ],
      ];
    return $build;
  }

}
