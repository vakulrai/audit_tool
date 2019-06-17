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
        'title' => t('Upload audit documents'),
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

    return $data;
}
