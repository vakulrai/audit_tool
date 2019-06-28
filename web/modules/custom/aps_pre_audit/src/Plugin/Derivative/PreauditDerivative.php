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
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    foreach ($roles as $key => $value) {
      $user_role = $value;
    }
    
    if($ref = \Drupal::request()->query->get('unit_reference')){
      $id = $ref;
    }
    else{
      $id = 0;
    }
    if($user_role == 'auditor' || $user_role == 'mr_admin'){
      $links['records'] = [
        'title' => 'Records',
        'route_name' => 'view.planned_audit_listing.document_list_records',
        'base_route' => 'view.planned_audit_listing.document_list_ia',
      ] + $base_plugin_definition;

      $links['manuals'] = [
        'title' => 'Manuals',
        'route_name' => 'view.planned_audit_listing.document_list_manuals',
        'base_route' => 'view.planned_audit_listing.document_list_ia',
      ] + $base_plugin_definition;
      
      $links['internal_documents'] = [
        'title' => 'Internal Documents',
        'route_name' => 'view.planned_audit_listing.document_list_ia',
        'base_route' => 'view.planned_audit_listing.document_list_ia',
      ] + $base_plugin_definition;
    }
    elseif ($user_role == 'auditee') {
      $links['records'] = [
        'title' => 'Records',
        'route_name' => 'view.planned_audit_listing.document_list_records',
        'base_route' => 'view.planned_audit_listing.document_list_records',
      ] + $base_plugin_definition;
    }
    $links['add_procedures'] = [
      'title' => 'Add Procedures',
      'route_name' => 'node.add',
      'base_route' => 'node.add',
      'route_parameters' => ['node_type' => 'procedures', 'unit_reference' => $id],
    ] + $base_plugin_definition;

    $links['procedure_listing'] = [
      'title' => 'Procedure Listing',
      'route_name' => 'view.procedure_listing.procedures_listing',
      'base_route' => 'node.add',
      'route_parameters' => ['unit_reference' => $id],
    ] + $base_plugin_definition;

    $links['unit'] = [
      'title' => 'Unit',
      'route_name' => 'view.user_registration_view.registration',
      'base_route' => 'view.user_registration_view.registration',
    ] + $base_plugin_definition;

    $links['business_process'] = [
      'title' => 'Business Processes',
      'route_name' => 'view.registered_unit_listing.bp',
      'base_route' => 'view.user_registration_view.registration',
    ] + $base_plugin_definition;

    $links['sections'] = [
      'title' => 'Sections',
      'route_name' => 'view.registered_unit_listing.section_list',
      'base_route' => 'view.user_registration_view.registration',
    ] + $base_plugin_definition;
    
    $links['audit_list'] = [
      'title' => 'Audit List',
      'route_name' => 'view.internal_audit_systems.audit_listing',
      'base_route' => 'view.internal_audit_systems.audit_listing',
      'route_parameters' => ['unit_reference' => $id],
    ] + $base_plugin_definition;

    $links['systems'] = [
      'title' => 'Systems',
      'route_name' => 'node.add',
      'base_route' => 'view.internal_audit_systems.audit_listing',
      'route_parameters' => ['unit_reference' => $id, 'node_type' => 'internal_audit'],
    ] + $base_plugin_definition;

    $links['external_audit_systems'] = [
      'title' => 'External Audit System',
      'route_name' => 'node.add',
      'base_route' => 'node.add',
      'route_parameters' => ['unit_reference' => $id, 'node_type' => 'external_audit_'],
    ] + $base_plugin_definition;

    $links['external_audit_clauses'] = [
      'title' => 'External Audit Standards',
      'route_name' => 'node.add',
      'base_route' => 'node.add',
      'route_parameters' => ['unit_reference' => $id, 'node_type' => 'clauses'],
    ] + $base_plugin_definition;

    return $links;
  }
}
