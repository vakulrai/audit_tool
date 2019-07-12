<?php

namespace Drupal\aps_pre_audit\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
/**
 * Provides a 'UploadMoreDocumentsForMradmmin' block.
 *
 * @Block(
 *  id = "upload_more_documents_for_mradmmin",
 *  admin_label = @Translation("Upload more documents for mradmmin"),
 * )
 */
class UploadMoreDocumentsForMradmmin extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
    $uri = explode('/', $current_uri);
    $build['#cache']['max-age'] = 0;
    switch ($uri[0]) {
    	case 'documentinternalrecords':
    	    $url = Url::fromRoute('node.add',['node_type' => 'internal_documents', 'id' => $uri[1], 'destination' => $current_uri]);
    		$build['info'] = [
		        '#type' => 'link',
		        '#title' => t('Upload Internal Document'),
		        '#url' => $url,
		        '#attributes' => [
		          'class' => [
		            'use-ajax',
		            'button',
		          ],
		        ],
            ];
    		break;

    	case 'documentrecords':
    	    $url = Url::fromRoute('node.add',['node_type' => 'pre_audit_records', 'id' => $uri[1], 'destination' => $current_uri]);
    	    $build['info'] = [
		        '#type' => 'link',
		        '#title' => t('Upload Records'),
		        '#url' => $url,
		        '#attributes' => [
		          'class' => [
		            'use-ajax',
		            'button',
		          ],
		        ],
            ];
    		
    		break;

    	case 'documentmanuals':
    	    $url = Url::fromRoute('node.add',['node_type' => 'pre_audit_manuals', 'id' => $uri[1], 'destination' => $current_uri]);
    		$build['info'] = [
		        '#type' => 'link',
		        '#title' => t('Upload Manuals'),
		        '#url' => $url,
		        '#attributes' => [
		          'class' => [
		            'use-ajax',
		            'button',
		          ],
		        ],
            ];
    		break;
    }
    return $build;
  }

}
