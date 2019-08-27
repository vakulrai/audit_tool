<?php

use Drupal\Core\Form\FormStateInterface;

function aps_form_install_configure_form_alter(&$form,FormStateInterface$form_state){
  // Date/time settings
  $form['server_settings']['site_default_country']['#default_value'] = 'IN';
  $form['server_settings']['date_default_timezone']['#default_value'] = 'Asia/Kolkata';
  $options_subscription = [
    'single' => 'Single',
    'group' => 'Group',
  ];

  $form['subscription'] = [
    '#type' => 'fieldset', 
    '#title' => t('Subscription'), 
    '#attributes' => ['id' => 'subscription'], 
    '#collapsible' => TRUE, 
    '#collapsed' => FALSE,
  ];

  $form['subscription']['subscription_type'] = [
    '#type' => 'select',
	'#options' => $options_subscription,
	'#required' => TRUE,
	'#title' => t('Subscription Type'),
   ];

   foreach (array_keys($form['actions']) as $action) {
      if ($action != 'preview' && isset($form['actions'][$action]['#type']) && $form['actions'][$action]['#type'] === 'submit') {
        $form['actions'][$action]['#submit'][] = 'generalOperationCallback';
      }
    }
}

function generalOperationCallback(&$form,FormStateInterface$form_state){
  //Create Mr admin With the credentials user enters.
  $form_values = $form_state->getUserInput();
  $values = array(
      // 'name' => $form_values['account']['name'],
  	  'name' => 'profile test',
      'mail' => $form_values['account']['mail'],
      'roles' => ['mr_admin'],
      'pass' => $form_values['account']['pass']['pass1'],
      'timezone'=> 'Asia/Kolkata',
      'status' => 1,
   );  
$account = entity_create('user', $values);
$account->save();
}