<?php

namespace Drupal\aps_general\EventSubscriber;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\user\Entity\Role;

/**
 * RoleBasedUserRedirect event subscriber.
 *
 * @package Drupal\aps_general\DefaultSubscriber
 */
class DefaultSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

   /**
   * @var Drupal\Core\Session\AccountProxy $account
   */
  protected $account;


  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $configFactory, AccountProxy $account) {
    $this->configFactory = $configFactory;
    $this->account = $account;
  }

  /**
   * Check redirection.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   Event.
   */
  public function checkRedirection(FilterResponseEvent $event) {
    global $base_url;
    $current_path = \Drupal::service('path.current')->getPath();
    $profile = [];

    if (!$uid) {
      $uid = $this->account->id();
    }
    $user = User::load($uid);
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    foreach ($roles as $key => $value) {
      $user_role = $value;
    }
    if($user_role == 'mr_admin'){
      // $role_object = Role::load('mr_admin');
      // $role_object->revokePermission('create unit content');
      // $role_object->save();
    }
    $user_unit = $user->field_reference_id->target_id;
    $front = \Drupal::service('path.matcher')->isFrontPage();
    $route_name = \Drupal::routeMatch()->getRouteName();
    if ($front && $user_role == 'auditor' || $user_role == 'auditor' && $route_name == 'entity.user.canonical') {
      $response = new RedirectResponse(URL::fromUserInput('/planned-audit-listing/'.$user_unit)->toString());  
      $response->send(); 
    }
    elseif ($front && $user_role == 'auditee'|| $user_role == 'auditee' && $route_name == 'entity.user.canonical') {
      $response = new RedirectResponse(URL::fromUserInput('/planned-audit-listing-auditee/'.$user_unit)->toString());  
      $response->send(); 
    }
    elseif($user_role == 'mr_admin' && $route_name == 'entity.user.canonical'){
      $response = new RedirectResponse('/home', 301);  
      $response->send(); 
    }

  }

  public function checkAuthStatus(GetResponseEvent $event) {
    if ($this->account->isAnonymous() && \Drupal::routeMatch()->getRouteName() != 'user.login'&& \Drupal::routeMatch()->getRouteName() != 'user.register') {

      // add logic to check other routes you want available to anonymous users,
      // otherwise, redirect to login page.
      $route_name = \Drupal::routeMatch()->getRouteName();
      if (strpos($route_name, 'view') === 0 && strpos($route_name, 'rest_') !== FALSE) {
        return;
      }

      $response = new RedirectResponse('/user/login', 301);
      $event->setResponse($response);
      $event->stopPropagation();
    }
  }
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['checkRedirection'];
    $events[KernelEvents::REQUEST][] = ['checkAuthStatus'];
    return $events;
  }

}

