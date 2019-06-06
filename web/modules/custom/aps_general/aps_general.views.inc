<?php

/**
 * @file
 * Contains aps_general\aps_general.views.inc..
 * Provide a custom views field data that isn't tied to any other module. */

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Render\Markup;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\system\ActionConfigEntityInterface;

/**
* Implements hook_views_data().
*/
function aps_general_views_data() {

    $data['views']['table']['group'] = t('Custom Global');
    $data['views']['table']['join'] = [
      // #global is a special flag which allows a table to appear all the time.
      '#global' => [],
    ];


    $data['views']['remainingday_event'] = [
        'title' => t('Remainingday event'),
        'help' => t('Field plugin to display the event remining days'),
        'field' => [
            'id' => 'remainingday_event',
        ],
    ];

    $data['views']['delta_qa'] = [
        'title' => t('Return entity button to add question'),
        'help' => t('Field plugin to display the Delta Q page'),
        'field' => [
            'id' => 'delta_qa',
        ],
    ];

    $data['views']['pre_audit_report'] = [
        'title' => t('Return entity button to report a document'),
        'help' => t('Return entity button to report a document'),
        'field' => [
            'id' => 'pre_audit_report',
        ],
    ];

    return $data;
}
