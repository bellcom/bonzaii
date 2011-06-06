<?php

namespace Bonzaii;
use Silex\Application;
use Silex\ExtensionInterface;

class TweetsExtension implements ExtensionInterface {
  public function register(Application $app) {
    $app['tweets'] = $app->share(function() use($app){
      // setup default
      if (empty($app['tweets.limit'])) {
        $app['tweets.limit'] = 10;
      }

      if (!isset($app['tweets.cache_dir'])) {
        $app['tweets.cache_dir'] = __DIR__ . '/../../data/cache/';
      }

      return new BonzaiiTweets($app);
    });
  }
}
