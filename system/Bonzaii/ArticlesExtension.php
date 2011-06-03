<?php

namespace Bonzaii;

use Silex\Application;
use Silex\ExtensionInterface;

class ArticlesExtension implements ExtensionInterface {
  public function register(Application $app) {
    $app['articles'] = $app->share(function() use($app){
      // setup default
      if (empty($app['articles.limit'])) {
        $app['articles.limit'] = 10;
      }

      return new BonzaiiArticles($app);
    });
  }
}
