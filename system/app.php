<?php
// running suhosin remember to set:
// suhosin.executor.include.whitelist="phar"

require_once __DIR__ . '/vendor/Silex/silex.phar';

$app = new Silex\Application();

// templating
$app->register(new Silex\Extension\TwigExtension(), array(
  'twig.path' => __DIR__ . '/templates',
  'twig.class_path' => __DIR__ . '/vendor/Twig/lib',
  // 'twig.options' => array('cache' => __DIR__ . '/../data/cache'),
  'twig.options' => array('cache' => FALSE),
));

// database abstraction
$app->register(new Silex\Extension\DoctrineExtension(), array(
  'db.options' => array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/../data/bonzaii.db',
  ),
  'db.dbal.class_path' => __DIR__ . '/vendor/doctrine-dbal/',
  'db.common.class_path' => __DIR__ . '/vendor/doctrine-dbal/',
));

// emailing
$app->register(new Silex\Extension\SwiftmailerExtension(), array(
  'swiftmailer.class_path' => __DIR__ . '/vendor/swiftmailer/'
));

$app->register(new Silex\Extension\SessionExtension());
if ($app['session']->hasFlash('message')) {
  $app['twig']->addGlobal('message', $app['session']->getFlash('message'));
}


// favicon fail..
$app->get('/favicon.ico', function() use ($app) {
  header('HTTP/1.1 404 Not Found', true, 404);
});

// setup autoloader
$app['autoloader']->registerNamespaces(array('Bonzaii' => __DIR__ . '/'));

//register global bonzaii extentions
$app->register(new Bonzaii\ArticlesExtension(), array(
  'articles.limit' => 10,
  'articles.image_upload_path' => __DIR__ . '/../public_html/fx/img/',
  'articles.image_path' => '/fx/img/',
));

// poor mans admin switch
if (strpos($_SERVER['REQUEST_URI'], '/admin/') === 0) {
  $app['twig']->addGlobal('layout', 'backend/layout-backend.html.twig');
  require __DIR__ . '/backend.php';
}
else {
  $app['twig']->addGlobal('layout', 'layout-frontend.html.twig');
  $app->register(new Bonzaii\TweetsExtension(), array(
    'tweets.limit' => 5,
    'tweets.search' => 'bellcomdk'
  ));

  require __DIR__ . '/frontend.php';
}

return $app;
