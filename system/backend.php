<?php

if (empty($app['tags'])) {
  $app->register(new Bonzaii\TagsExtension(), array());
}
$app->register(new Bonzaii\AccessExtension(), array());

require __DIR__ . '/Bonzaii/Backend/app.php';
