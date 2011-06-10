<?php

namespace Bonzaii;

use Silex\Application;
use Silex\ExtensionInterface;

class RedirectsExtension implements ExtensionInterface {
  public function register(Application $app) {
    $app['redirects'] = $app->share(function() use($app){
      return new BonzaiiRedirects($app);
    });
  }
}
