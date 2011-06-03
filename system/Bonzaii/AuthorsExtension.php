<?php

namespace Bonzaii;

use Silex\Application;
use Silex\ExtensionInterface;

class AuthorsExtension implements ExtensionInterface {
  public function register(Application $app) {
    $app['authors'] = $app->share(function() use($app){
      return new BonzaiiAuthors($app);
    });
  }
}
