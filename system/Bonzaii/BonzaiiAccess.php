<?php

namespace Bonzaii;
use Silex\Application;

class BonzaiiAccess {
  protected $app;
  protected $params = array();

  protected $allowed = array(
    'GET_admin_account_login',
    'POST_admin_account_login',
  );

  public function __construct(Application $app) {
    $this->app = $app;
  }

  public function check() {
    $route = $this->app['request']->attributes->get('_route');

    if (!in_array($route, $this->allowed) && !$this->app['session']->get('loggedin')) {
      return false;
    }
    return true;
  }

  public function login() {
    $username = $this->app['request']->get('username');
    $password = $this->app['request']->get('password');

    if (!empty($username) && !empty($password)) {
      $user = $this->app['authors']->get(array(
        'email' => $username,
        'password' => md5($password),
      ), true);

      if (is_array($user)) {
        $this->app['session']->set('loggedin', true);
        return true;
      }
    }

    return false;
  }
}
