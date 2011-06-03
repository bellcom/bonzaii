<?php

namespace Bonzaii;

use Silex\Application;
use Silex\ExtensionInterface;

class AccessExtension implements ExtensionInterface {
  public function register(Application $app) {
    $app['access'] = $app->share(function() use($app){
      return new BonzaiiAccess($app);
    });
  }
}
