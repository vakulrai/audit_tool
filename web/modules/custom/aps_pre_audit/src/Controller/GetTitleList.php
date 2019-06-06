<?php
namespace Drupal\aps_pre_audit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RedirectForm.
 */
class GetTitleList extends ControllerBase {

  public function getTitle(Request $request) {
    $results = [];
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));
      $query = \Drupal::database()->select('node_field_data', 'n');
      $query->fields('n', ['title','nid']);
      $query->condition('n.type', 'planned_events');
      $query->condition('n.title', db_like($typed_string) . '%', 'LIKE');
      $nids = $query->execute()->fetchAll();

      foreach ($nids as $entity) {
        $node_object = Node::load($entity->nid);
        $results[$node_object->get('nid')->value] = $node_object->get('title')->value;
      }
    }
    return new JsonResponse(array_values(array_unique($results)));
  }

}	
?>