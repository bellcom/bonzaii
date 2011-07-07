<?php
/**
 * if you are running suhosin remember to set:
 * suhosin.executor.include.whitelist="phar"
 * in your php.ini file
 */

require_once __DIR__ . '/vendor/Silex/silex.phar';
$app = new Silex\Application();


/**
 * setup twig
 */
$app->register(new Silex\Extension\TwigExtension(), array(
  'twig.path' => __DIR__ . '/templates',
  'twig.class_path' => __DIR__ . '/vendor/Twig/lib',
  // 'twig.options' => array('cache' => __DIR__ . '/../data/cache'),
  'twig.options' => array('cache' => FALSE),
));


/**
 * setup doctrine
 */
$app->register(new Silex\Extension\DoctrineExtension(), array(
  'db.options' => array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/../data/bonzaii.db',
  ),
  'db.dbal.class_path' => __DIR__ . '/vendor/doctrine-dbal/lib/',
  'db.common.class_path' => __DIR__ . '/vendor/doctrine-dbal/lib/vendor/doctrine-common/lib/',
));


/**
 * setup swiftmailer
 */
$app->register(new Silex\Extension\SwiftmailerExtension(), array(
  'swiftmailer.class_path' => __DIR__ . '/vendor/swiftmailer/'
));


/**
 * setup sesion handeling
 */
$app->register(new Silex\Extension\SessionExtension());
if ($app['session']->hasFlash('message')) {
  $app['twig']->addGlobal('message', $app['session']->getFlash('message'));
}


/**
 * add bonzaii autoloader
 */
$app['autoloader']->registerNamespaces(array('Bonzaii' => __DIR__ . '/'));


/**
 * register bonzaii extentions
 */
$app->register(new Bonzaii\ArticlesExtension(), array(
  'articles.limit' => 10,
  'articles.image_upload_path' => __DIR__ . '/../public_html/fx/img/',
  'articles.image_path' => '/fx/img/',
));
$app->register(new Bonzaii\RedirectsExtension(), array());
$app->register(new Bonzaii\AuthorsExtension(), array(
  'authors.image_upload_path' => __DIR__ . '/../public_html/fx/img/',
  'authors.image_path' => '/fx/img/',
));


/**
 * this is a poor-mans admin switch
 */
if (strpos($_SERVER['REQUEST_URI'], '/admin/') === 0) {
  $app['is_backend'] = true;
  $app['twig']->addGlobal('layout', 'backend/layout-backend.html.twig');
  require __DIR__ . '/backend.php';
}
else {
  $app['is_backend'] = false;
  $app['twig']->addGlobal('layout', 'layout-frontend.html.twig');
  $app->register(new Bonzaii\TweetsExtension(), array(
    'tweets.limit' => 5,
    'tweets.search' => 'bellcomdk'
  ));
  require __DIR__ . '/frontend.php';
}


/**
 * kill favicon requests
 */
$app->get('/favicon.ico', function() use ($app) {
  header('HTTP/1.1 404 Not Found', true, 404);
});


/**
 * before actions is actions handled before any request is handled
 * note that the request is parsed by silex at this point, so any tweaks should be handled before calling on silex.
 */
$app->before(function() use ($app) {
  // login check
  if ($app['is_backend'] && !$app['access']->check()) {
    // for some reason we cannot return an $app->redirect() here
    header('Location: /admin/account/login');
    exit;
  }
});


/**
 * catch errors
 * here we use the redirects bundle to handle any known redirects
 */
$app->error(function(Exception $e) use ($app){

  $methods = get_class_methods($e);
  if (isset($methods['getStatusCode'])) {
    if ($e->getStatusCode() == 404) {
      $path = trim($app['request']->getRequestUri(), '/');
      if ($to = $app['redirects']->find($path)) {
        // send 301 to tell google and others that the page has moved.
        return $app->redirect($to, 301);
      }
      // send unknown requests to the error log, maby someone is watching
      error_log('This path has no march: "' . $path . '"');
    }
  }
  else{
    error_log(__FILE__ . ' +' . __LINE__ . ' : ' . print_r($e->getMessage(), 1));
    return '500';
  }

  return $app->redirect('/');
});

return $app;
