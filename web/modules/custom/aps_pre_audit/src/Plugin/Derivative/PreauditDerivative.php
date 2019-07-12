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

    if($type_id = \Drupal::request()->query->get('type')){
      $type = $type_id;
    }
    else{
      $type = 0;
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

    $links['department'] = [
      'title' => 'Departments',
      'route_name' => 'view.registered_unit_listing.department_listing',
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

    if($id){
      $audit_criteria_query = \Drupal::entityQuery('audit_criteria');
      $audit_criteria_query->condition('field_unit_reference', $id);
      $audit_criteria_id = $audit_criteria_query->execute();

      $audit_criteria_process_query = \Drupal::entityQuery('audit_criteria_process');
      $audit_criteria_process_query->condition('field_unit_reference', $id);
      $audit_criteria_process_id = $audit_criteria_process_query->execute();

      $audit_criteria_product_query = \Drupal::entityQuery('audit_criteria_product');
      $audit_criteria_product_query->condition('field_unit_reference', $id);
      $audit_criteria_product_id = $audit_criteria_product_query->execute();
      
      if($type == 'edit'){
        $base_route = 'entity.audit_criteria.edit_form';
      }
      else{
        $base_route = 'eck.entity.add';
      }
      if(count($audit_criteria_id)){
        $links['audit_criteria_systems'] = [
          'title' => 'Systems',
          'route_name' => 'entity.audit_criteria.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_criteria' => key($audit_criteria_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_criteria_systems'] = [
          'title' => 'Systems',
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_criteria', 'eck_entity_bundle' => 'systems', 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }

      if(count($audit_criteria_process_id)){
        $links['audit_criteria_process'] = [
          'title' => 'Process',
          'route_name' => 'entity.audit_criteria_process.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_criteria_process' => key($audit_criteria_process_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_criteria_process'] = [
          'title' => 'Process',
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_criteria_process', 'eck_entity_bundle' => 'process', 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }

      if(count($audit_criteria_product_id)){
         $links['audit_criteria_product'] = [
          'title' => 'Product',
          'route_name' => 'entity.audit_criteria_product.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_criteria_product' => key($audit_criteria_product_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_criteria_product'] = [
          'title' => 'Product',
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_criteria_product', 'eck_entity_bundle' => 'product', 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }

    }

    $links['ia_system'] = [
      'title' => 'Systems',
      'route_name' => 'view.internal_audit_systems.ia_systems',
      'base_route' => 'view.internal_audit_systems.ia_systems',
      'route_parameters' => ['unit_reference' => $id],
    ] + $base_plugin_definition;

    $links['ia_process'] = [
      'title' => 'Process',
      'route_name' => 'view.internal_audit_systems.ia_process',
      'base_route' => 'view.internal_audit_systems.ia_systems',
      'route_parameters' => ['unit_reference' => $id],
    ] + $base_plugin_definition;

    $links['ia_product'] = [
      'title' => 'Product',
      'route_name' => 'view.internal_audit_systems.ia_product',
      'base_route' => 'view.internal_audit_systems.ia_systems',
      'route_parameters' => ['unit_reference' => $id],
    ] + $base_plugin_definition;    
    return $links;
  }
}
