<?php

// login check
$app->before(function() use ($app) {
  if (!$app['access']->check()) {
    header('Location: /admin/account/login');
    exit;
  }
});

if (empty($app['authors'])) {
  $app->register(new Bonzaii\AuthorsExtension(), array());
}
$app->register(new Bonzaii\AccessExtension(), array());

require __DIR__ . '/Bonzaii/Backend/app.php';