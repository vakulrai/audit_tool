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


    $data['views']['delta_qa'] = [
        'title' => t('Add Delta Q'),
        'help' => t('Add delta Q'),
        'field' => [
            'id' => 'delta_qa',
        ],
    ];

    $data['views']['pre_audit_report'] = [
        'title' => t('Document Report'),
        'help' => t('Upload button to upload doc. duing audit'),
        'field' => [
            'id' => 'pre_audit_report',
        ],
    ];

    $data['views']['remainingday_event'] = [
        'title' => t('Remaining Days for event'),
        'help' => t('Upload button to upload doc. duing audit'),
        'field' => [
            'id' => 'remainingday_event',
        ],
    ];
    
    $data['views']['upload_audit_documents'] = [
        'title' => t('Upload audit documents'),
        'help' => t('Upload button to upload doc. duing audit'),
        'field' => [
            'id' => 'upload_audit_documents',
        ],
    ];

    $data['views']['remainingday_event_validate'] = [
        'title' => t('Check user can start Pre-audit'),
        'help' => t('Validate Pre-Audit'),
        'field' => [
            'id' => 'remainingday_event_validate',
        ],
    ];

    $data['views']['kpi_status'] = [
        'title' => t('Provide checkbox to check KPI is achieved or not'),
        'help' => t('Validate Pre-Audit'),
        'field' => [
            'id' => 'kpi_status',
        ],
    ];

    return $data;
}
