<?php

namespace Drupal\aps_pre_audit\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all entity bundles.
 */
class PreauditDerivative extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

   /**
   * @var EntityTypeManagerInterface $entityTypeManager.
   */
  protected $entityTypeManager;
 
  /**
   * Creates a ProductMenuLink instance.
   *
   * @param $base_plugin_id
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct($base_plugin_id, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }
 
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_type.manager')
    );
  }
 
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    $links[0] = [
      'title' => 'Records',
      'route_name' => 'view.planned_audit_listing.document_list_records',
      'base_route' => 'view.planned_audit_listing.document_list_ia',
    ] + $base_plugin_definition;

    $links[1] = [
      'title' => 'Manuals',
      'route_name' => 'view.planned_audit_listing.document_list_manuals',
      'base_route' => 'view.planned_audit_listing.document_list_ia',
    ] + $base_plugin_definition;
    
    $links[2] = [
      'title' => 'Internal Documents',
      'route_name' => 'view.planned_audit_listing.document_list_ia',
      'base_route' => 'view.planned_audit_listing.document_list_ia',
    ] + $base_plugin_definition;

    $links[3] = [
      'title' => 'Add Procedures',
      'route_name' => 'node.add',
      'base_route' => 'node.add',
      'parameters' => ['node_type' => 'procedures'],
    ] + $base_plugin_definition;

    $links[4] = [
      'title' => 'Procedure Listing',
      'route_name' => 'view.procedure_listing.procedures_list',
      'base_route' => 'view.procedure_listing.procedures_list',
      'query' => ['node_type' => 'procedures'],
    ] + $base_plugin_definition;

    $links[5] = [
      'title' => 'Unit',
      'route_name' => 'view.user_registration_view.registration',
      'base_route' => 'view.user_registration_view.registration',
    ] + $base_plugin_definition;

    $links[6] = [
      'title' => 'Business Processes',
      'route_name' => 'view.registered_unit_listing.bp',
      'base_route' => 'view.user_registration_view.registration',
    ] + $base_plugin_definition;

    $links[7] = [
      'title' => 'Sections',
      'route_name' => 'view.registered_unit_listing.section_list',
      'base_route' => 'view.user_registration_view.registration',
    ] + $base_plugin_definition;

    return $links;
  }
}
