<?php

namespace Bonzaii;

use Silex\Application;
use Silex\ExtensionInterface;

class TagsExtension implements ExtensionInterface {
  public function register(Application $app) {
    $app['tags'] = $app->share(function() use($app){
      return new BonzaiiTags($app);
    });
  }
}
