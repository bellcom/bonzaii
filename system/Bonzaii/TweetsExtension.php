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

      return new BonzaiiTweets($app);
    });
  }
}
