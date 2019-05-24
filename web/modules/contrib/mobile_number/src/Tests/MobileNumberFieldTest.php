<?php

namespace Drupal\mobile_number\Tests;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormState;
use Drupal\Core\Language\Language;
use Drupal\field\Entity\FieldConfig;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Mobile number field functionality.'
 *
 * @group mobile_number
 */
class MobileNumberFieldTest extends WebTestBase {
  
  static $modules = array('mobile_number','sms', 'node');
  
  /**
   * Mobile number util.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  public $util;
  
  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  public $flood;
  
  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->util = \Drupal::service('mobile_number.util');
    $this->flood = \Drupal::service('flood');
  }

  /**
   * Test number validation.
   */
  public function testNumberUniqueness() {
    $tries = array(
      'New values',
      'Resubmit values',
    );

    $value_count = array(
      1 => 'One value',
      2 => 'Two values',
    );

    $number_types = array(
      'Unverified',
      'Verified',
    );

    $unique_types = array(
      MobileNumberUtilInterface::MOBILE_NUMBER_UNIQUE_YES => 'Unique',
      MobileNumberUtilInterface::MOBILE_NUMBER_UNIQUE_YES_VERIFIED => 'Unique verified',
    );

    $names = [];
    foreach ($value_count as $count => $count_text) {
      foreach ($unique_types as $unique => $unique_text) {
        $name = 'unique_' . $unique . '_count_' . $count;
        $this->drupalCreateContentType(array('type' => $name));
        $this->createField($name, "field_$name", $unique, $count);
        $names[] = $name;
      }
    }
  
  
    $user = $this->drupalCreateUser(array_map(function($element){ return "create $element content";}, $names));
    $this->setCurrentUser($user);

    // Check for in-field duplicates.
//    foreach ($unique_types as $unique => $unique_text) {
//      $name = 'unique_' . $unique . '_count_2';
//      foreach ($number_types as $verified => $verified_text) {
//        foreach ($number_types as $verified2 => $verified_text2) {
//          $errors = $this->createMobileNumberNode($name, "+9725411111$verified$verified2", $verified, $verified2);
//          $valid = FALSE;
//          switch ($unique) {
//            case MobileNumberUtilInterface::MOBILE_NUMBER_UNIQUE_YES_VERIFIED:
//              $valid = !($verified || $verified2);
//              break;
//
//          }
//          $valid_text = $valid ? 'is unique' : 'is not unique';
//          $this->assertTrue($valid == !$errors, "New values, Two values, $unique_text, 1 = $verified_text, 2 = $verified_text2: $valid_text. " . ($errors ?reset($errors) : ''), 'Number Uniqueness');
//        }
//      }
//    }

    // Check for inter-entity multi-value duplicates.
    foreach ($unique_types as $unique => $unique_text) {
      $name = 'unique_' . $unique . '_count_2';
      $count = 0;
      foreach ($number_types as $existing_verified => $existing_verified_text) {
        foreach ($number_types as $verified => $verified_text) {
          $this->createMobileNumberNode($name, "+9725422222$existing_verified$verified", $existing_verified);
          $errors = $this->createMobileNumberNode($name, "+9725422222$existing_verified$verified", $verified);
          $valid = FALSE;
          switch ($unique) {
            case MobileNumberUtilInterface::MOBILE_NUMBER_UNIQUE_YES_VERIFIED:
              $valid = ($verified + $existing_verified) < 2;
              break;
        
          }
          $valid_text = $valid ? 'is unique' : 'is not unique';
          $this->assertTrue($valid == !$errors, "Resubmit values, One value, $unique_text, $verified_text, existing = $existing_verified_text: $valid_text.", 'Number Uniqueness');
          $count++;
        }
      }
    }

    // Check for inter-entity single-value duplicates.
    foreach ($unique_types as $unique => $unique_text) {
      $name = 'unique_' . $unique . '_count_1';
      foreach ($number_types as $existing_verified => $existing_verified_text) {
        foreach ($number_types as $verified => $verified_text) {
          $number = "+9725433333$existing_verified$verified";
          $this->createMobileNumberNode($name, $number, $existing_verified);
          $errors = $this->createMobileNumberNode($name, $number, $verified);
          $valid = FALSE;
          switch ($unique) {
            case MobileNumberUtilInterface::MOBILE_NUMBER_UNIQUE_YES_VERIFIED:
              $valid = ($verified + $existing_verified) < 2;
              break;
          }
          $valid_text = $valid ? 'is unique' : 'is not unique';
          $this->assertTrue($valid == !$errors, "Resubmit values, One value, $unique_text, presaved = $existing_verified_text, new = $verified_text, $valid_text. " . ($errors ?reset($errors) : ''), 'Number Uniqueness');
        }
      }
    }
  }
  
  
  
  /**
   * Test number verification.
   */
  public function testVerification() {
    $number = '0541234567';
    $country = 'IL';
    $value = '+972541234567';
    $mobile_number = $this->util->getMobileNumber($value);
    $code = $this->util->generateVerificationCode();
    
    $required_name = 'ver_required';
    $this->drupalCreateContentType(array('type' => $required_name));
    $this->createField($required_name, "field_$required_name", MobileNumberUtilInterface::MOBILE_NUMBER_UNIQUE_NO, 1, MobileNumberUtilInterface::MOBILE_NUMBER_VERIFY_REQUIRED);
    $optional_name = 'ver_optional';
    $this->drupalCreateContentType(array('type' => $optional_name));
    $this->createField($optional_name, "field_$optional_name", MobileNumberUtilInterface::MOBILE_NUMBER_UNIQUE_NO, 1, MobileNumberUtilInterface::MOBILE_NUMBER_VERIFY_OPTIONAL);
    
    $tokens = array(
      FALSE => 'Wrong token',
      NULL => 'No token',
      TRUE => 'Correct token',
    );
    
    $codes = array(
      '000' => 'Wrong code',
      NULL => 'No code',
      $code => 'Correct code',
    );
  
    $user = $this->drupalCreateUser(array("create $required_name content", "create $optional_name content"));
    $admin = $this->drupalCreateUser(array("create $required_name content", 'bypass mobile number verification requirement'));
    
    $input['country-code'] = $country;
    $input['mobile'] = $number;
    
    $this->setCurrentUser($admin);
    $errors = $this->createMobileNumberNode($required_name, $value, FALSE);
    $this->assertTrue(!$errors, "Admin bypass verification requirement.", 'Number Verification');
    
    $this->setCurrentUser($user);
    $errors = $this->createMobileNumberNode($required_name, $value, FALSE);
    $this->assertTrue($errors, "Bypass verification requirement blocked.", 'Number Verification');
    
    $errors = $this->createMobileNumberNode($optional_name, $value, FALSE);
    $this->assertTrue(!$errors, "Optional verification allowed unverified.", 'Number Verification');
  
    /** @var \Drupal\Core\Flood\FloodInterface $flood */
    $flood = \Drupal::service('flood');
    foreach ($tokens as $is_valid_token => $t) {
      foreach ($codes as $input_code => $c) {
        $input['country-code'] = $country;
        $input['mobile'] = $number;
        $input['verification_token'] = isset($is_valid_token) ? ($is_valid_token ? $this->util->registerVerificationCode($mobile_number, $code) : 'abc') : NULL;
        $input['verification_code'] = $input_code;
        $flood->clear('mobile_number_verification', $value);
        $errors = $this->createMobileNumberNodeFromInput($required_name, $input);
        
        $validated = ($is_valid_token) && ($code == $input_code);
        
        $valid_text = $validated ? 'verified' : 'not verified';
        $this->assertTrue($validated == !$errors, "$t, $c, is $valid_text. " . ($errors ? reset($errors) : ''), 'Number Verification');
      }
    }
  
    $input = [
      'country-code' => $country,
      'mobile' => $value,
    ];
    $_SESSION['mobile_number_verification'][$value]['verified'] = TRUE;
    $errors = $this->createMobileNumberNodeFromInput($required_name, $input);
    $this->assertTrue(!$errors, "Already verified number is verified." . ($errors ? reset($errors) : ''), 'Number Verification');
    
    $input = [
      'country-code' => $country,
      'mobile' => substr($number, 0, 9) . '0',
    ];
    $errors = $this->createMobileNumberNodeFromInput($required_name, $input);
    $this->assertTrue($errors, "Not yet verified number is not verified. " . ($errors ? reset($errors) : ''), 'Number Verification');
  }
  
  /**
   * Test tfa option.
   */
//  public function testTfa() {
//    $number = '0541234567';
//    $country = 'IL';
//    $value = '+972541234567';
//    $mobile_number = $this->util->getMobileNumber($value);
//    $code = $this->util->generateVerificationCode();
//    $token = $this->util->registerVerificationCode($mobile_number, $code);
//
//    $element = array(
//      '#type' => 'mobile_number',
//      '#title' => 'M',
//      '#mobile_number' => array('tfa' => TRUE),
//    );
//
//    $input['tfa'] = 1;
//    $input['country-code'] = $country;
//    $input['mobile'] = $number;
//
//    $element['#default_value']['verified'] = 0;
//    $errors = $this->submitFormElement($element, $input, $value);
//    $this->assertTrue($errors, "Two factor authentication enabling failure.", 'TFA enabling');
//
//    $input['verification_token'] = $token;
//    $input['verification_code'] = $code;
//    $errors = $this->submitFormElement($element, $input, $value);
//    $this->assertTrue(!$errors, "Two factor authentication enabled successfully.", 'TFA enabling');
//  }

  /**
   * Create node with mobile number(s).
   */
  public function createMobileNumberNode($name, $number, $verified, $verified2 = NULL) {
    $values = array();
    $values["field_$name"][0] = array(
      'mobile' => $number,
      'country-code' => 'IL',
    );
    $mobile_number = $this->util->getMobileNumber($number);
    if ($verified) {
      $values["field_$name"][0]['verification_code'] = $code = $this->util->generateVerificationCode();
      $values["field_$name"][0]['verification_token'] = $this->util->registerVerificationCode($mobile_number, $code);
    }
    if (isset($verified2)) {
      $values["field_$name"][1] = array(
        'mobile' => $number,
        'country-code' => 'IL',
      );
      if ($verified2) {
        $values["field_$name"][1]['verification_code'] = $code = $this->util->generateVerificationCode();
        $values["field_$name"][1]['verification_token'] = $this->util->registerVerificationCode($mobile_number, $code);
      }
    }

    return $this->submitNodeForm($name, $values, $number);
  }
  /**
   * Create node with mobile number(s).
   */
  public function createMobileNumberNodeFromInput($name, $input) {
    $values = array();
    $values["field_$name"][0] = $input;
    $mobile_number = $this->util->getMobileNumber($input['mobile'], $input['country-code']);
    return $this->submitNodeForm($name, $values, $this->util->getCallableNumber($mobile_number));
  }

  /**
   * Submit node form.
   */
  public function submitNodeForm($node_type, $values, $number) {

    $values += array(
      'body'      => array(Language::LANGCODE_NOT_SPECIFIED => array(array())),
      'title'     => $this->randomMachineName(8),
      'comment'   => 2,
      'changed'   => REQUEST_TIME,
      'moderate'  => 0,
      'promote'   => 0,
      'revision'  => 1,
      'log'       => '',
      'status'    => 1,
      'sticky'    => 0,
      'type'      => $node_type,
      'revisions' => NULL,
      'language'  => Language::LANGCODE_NOT_SPECIFIED,
    );
  
    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create($values);
  
    $form = \Drupal::entityTypeManager()
      ->getFormObject('node', 'default')
      ->setEntity($node);

    $form_state = new FormState();
    $form_state->setValues($values);
    $form_state->setValue('op', t('Save'));
    $form_state->setProgrammedBypassAccessCheck(TRUE);
    $form_state->setCached(FALSE);
    \Drupal::formBuilder()->submitForm($form, $form_state);

    unset($_SESSION['mobile_number_verification'][$number]['verified']);

    return $form_state->getErrors();
  }

  /**
   * Create mobile number field.
   */
  public function createField($content_type, $field_name, $unique, $cardinality, $verify = MobileNumberUtilInterface::MOBILE_NUMBER_VERIFY_OPTIONAL) {
    $entity_type_manager = \Drupal::entityTypeManager();
  
    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    $field_storage = $entity_type_manager->getStorage('field_storage_config')
      ->create([
        'entity_type' => 'node',
        'field_name' => $field_name,
        'type' => 'mobile_number',
      ]);
    $field_storage->setSetting('unique', $unique);
    $field_storage
      ->setCardinality($cardinality)
      ->save();
  
    // Create the instance on the bundle.
    $instance = array(
      'field_name' => $field_name,
      'entity_type' => 'node',
      'label' => 'Mobile Number',
      'bundle' => $content_type,
      'required' => TRUE,
    );
  
    FieldConfig::create($instance)->setSetting('verify', $verify)->save();
    
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity_form_display */
    $entity_form_display = EntityFormDisplay::load('node.' . $content_type . '.default');
    if (!$entity_form_display) {
      $entity_form_display = EntityFormDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => $content_type,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }
    $entity_form_display->save();
  
    $entity_form_display
      ->setComponent($field_storage->getName(), ['type' => 'mobile_number_default'])
      ->save();
  }

}
