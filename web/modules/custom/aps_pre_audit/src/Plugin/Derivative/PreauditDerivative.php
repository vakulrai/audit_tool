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
    $route_name = \Drupal::routeMatch()->getRouteName();
    $roles = $current_user->getRoles();
    $current_uri = trim(\Drupal::request()->getRequestUri(), '/');
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
      // $links['records'] = [
      //   'title' => 'Records',
      //   'route_name' => 'view.planned_audit_listing.document_list_records',
      //   'base_route' => 'view.planned_audit_listing.document_list_ia',
      //   'route_parameters' => ['unit_reference' => $id],
      // ] + $base_plugin_definition;

      $links['manuals'] = [
        'title' => 'Manuals',
        'route_name' => 'view.planned_audit_listing.document_list_manuals',
        'base_route' => 'view.planned_audit_listing.document_list_ia',
        'route_parameters' => ['unit_reference' => $id],
      ] + $base_plugin_definition;
      
      $links['internal_documents'] = [
        'title' => 'Internal Documents',
        'route_name' => 'view.planned_audit_listing.document_list_ia',
        'base_route' => 'view.planned_audit_listing.document_list_ia',
        'route_parameters' => ['unit_reference' => $id],
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

    $links['csv_operations'] = [
      'title' => 'Import CSV',
      'route_name' => 'aps_general.csv_entity_import',
      'base_route' => 'node.add',
      'route_parameters' => ['unit_reference' => $id, 'reference' => $id],
    ] + $base_plugin_definition;

    $links['customer_listing'] = [
      'title' => 'Customer Listing',
      'route_name' => 'view.customer_listing.customer_listing',
      'base_route' => 'node.add',
      'route_parameters' => ['unit_reference' => $id],
    ] + $base_plugin_definition;

    $links['add_customer'] = [
      'title' => 'Add Customer',
      'weight' => 0,
      'route_name' => 'node.add',
      'base_route' => 'node.add',
      'route_parameters' => ['node_type' => 'customers_manual', 'unit_reference' => $id],
    ] + $base_plugin_definition;

    // $links['unit'] = [
    //   'title' => 'Unit',
    //   'route_name' => 'view.user_registration_view.registration',
    //   'base_route' => 'view.user_registration_view.registration',
    // ] + $base_plugin_definition;

    // $links['business_process'] = [
    //   'title' => 'Business Processes',
    //   'route_name' => 'view.registered_unit_listing.bp',
    //   'base_route' => 'view.user_registration_view.registration',
    // ] + $base_plugin_definition;

    // $links['department'] = [
    //   'title' => 'Departments',
    //   'route_name' => 'view.registered_unit_listing.department_listing',
    //   'base_route' => 'view.user_registration_view.registration',
    // ] + $base_plugin_definition;

    // $links['sections'] = [
    //   'title' => 'Sections',
    //   'route_name' => 'view.registered_unit_listing.section_list',
    //   'base_route' => 'view.user_registration_view.registration',
    // ] + $base_plugin_definition;
    
    $links['audit_list'] = [
      'title' => 'Audit List',
      'route_name' => 'view.internal_audit_systems.audit_listing',
      'base_route' => 'view.internal_audit_systems.audit_listing',
      'route_parameters' => ['unit_reference' => $id],
    ] + $base_plugin_definition;

    $links['external_audit_detail_list'] = [
      'title' => 'External Audit Details',
      'route_name' => 'view.external_audit_listing.details',
      'base_route' => 'view.external_audit_listing.details',
      'route_parameters' => ['unit_reference' => $id],
    ] + $base_plugin_definition;

    $links['external_audit_standard_list'] = [
      'title' => 'External Audit Standard',
      'route_name' => 'view.external_audit_listing.standards',
      'base_route' => 'view.external_audit_listing.standards',
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

      $audit_criteria_external = \Drupal::entityQuery('audit_criteria_external');
      $audit_criteria_external->condition('field_unit_reference', $id);
      $audit_criteria_external_id = $audit_criteria_external->execute();

      $audit_criteria_supplier = \Drupal::entityQuery('audit_criteria_supplier');
      $audit_criteria_supplier->condition('field_unit_reference', $id);
      $audit_criteria_supplier_id = $audit_criteria_supplier->execute();

      $audit_criteria_customer = \Drupal::entityQuery('audit_criteria_customer');
      $audit_criteria_customer->condition('field_unit_reference', $id);
      $audit_criteria_customer_id = $audit_criteria_customer->execute();

      $audit_criteria_score = \Drupal::entityQuery('audit_criteria_score');
      $audit_criteria_score->condition('field_unit_reference', $id);
      $audit_criteria_score_id = $audit_criteria_score->execute();

      $audit_cycle= \Drupal::entityQuery('audit_cycle');
      $audit_cycle->condition('field_unit_reference', $id);
      $audit_cycle_id = $audit_cycle->execute();
      
      if($type == 'edit'){
        $base_route = 'entity.audit_criteria.edit_form';
      }
      else{
        $base_route = 'eck.entity.add';
      }
      if(count($audit_criteria_id)){
        $links['audit_criteria_internal'] = [
          'title' => 'Internal',
          'weight' => 1,
          'route_name' => 'entity.audit_criteria.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_criteria' => key($audit_criteria_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_criteria_internal'] = [
          'title' => 'Internal',
          'weight' => 1,
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_criteria', 'eck_entity_bundle' => 'systems', 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }

      if(count($audit_criteria_external_id)){
        $links['audit_criteria_external'] = [
          'title' => 'External',
          'weight' => 2,
          'route_name' => 'entity.audit_criteria_external.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_criteria_external' => key($audit_criteria_external_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_criteria_external'] = [
          'title' => 'External',
          'weight' => 2,
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_criteria_external', 'eck_entity_bundle' => 'external', 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }

      if(count($audit_criteria_customer_id)){
         $links['audit_criteria_customer'] = [
          'title' => 'Customer',
          'weight' => 3,
          'route_name' => 'entity.audit_criteria_customer.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_criteria_customer' => key($audit_criteria_customer_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_criteria_customer'] = [
          'title' => 'Customer',
          'weight' => 3,
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_criteria_customer', 'eck_entity_bundle' => 'customer', 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }

       if(count($audit_criteria_supplier_id)){
         $links['audit_criteria_product_supplier'] = [
          'title' => 'Supplier',
          'weight' => 4,
          'route_name' => 'entity.audit_criteria_supplier.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_criteria_supplier' => key($audit_criteria_supplier_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_criteria_product_supplier'] = [
          'title' => 'Supplier',
          'weight' => 4,
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_criteria_supplier', 'eck_entity_bundle' => 'supplier', 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }

      if(count($audit_criteria_score_id)){
         $links['audit_criteria_score'] = [
          'title' => 'Score Settings',
          'weight' => 5,
          'route_name' => 'entity.audit_criteria_score.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_criteria_score' => key($audit_criteria_score_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_criteria_score'] = [
          'title' => 'Score Settings',
          'weight' => 5,
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_criteria_score', 'eck_entity_bundle' => 'score', 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }

      if(count($audit_cycle_id)){
         $links['audit_time_cycle'] = [
          'title' => 'Time Cycle Settings',
          'weight' => 6,
          'route_name' => 'entity.audit_cycle.edit_form',
          'base_route' => $base_route,
          'route_parameters' => ['audit_cycle' => key($audit_cycle_id), 'unit_reference' => $id, 'type' => $type],
        ] + $base_plugin_definition;
      }
      else{
        $links['audit_time_cycle'] = [
          'title' => 'Time Cycle Settings',
          'weight' => 6,
          'route_name' => 'eck.entity.add',
          'base_route' => $base_route,
          'route_parameters' => ['eck_entity_type' => 'audit_cycle', 'eck_entity_bundle' => 'settings', 'unit_reference' => $id, 'type' => $type],
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

    if($user_role == 'mr_admin' || $user_role == 'group_mr'){
      if($type == 'default'){
        $route = 'view.user_listing.page_1';
      }
      elseif($type == 'auditor'){
        $route = 'view.user_listing.page_2';
      }
      elseif($type == 'mr'){
        $route = 'view.user_listing.page_3';
      }
      if($route != ''){
        $links['auditor_add'] = [
          'title' => 'Auditor Listing',
          'route_name' => $route,
          'base_route' => 'user.admin_create',
          'route_parameters' => ['type' => $type, 'unit_reference' => $id],
        ] + $base_plugin_definition;

        $links['auditor_list'] = [
          'title' => 'Add Auditor',
          'route_name' => 'user.admin_create',
          'base_route' => 'user.admin_create',
          'route_parameters' => ['type' => $type, 'unit_reference' => $id],
        ] + $base_plugin_definition;
      }
    }    
    return $links;
  }
}
