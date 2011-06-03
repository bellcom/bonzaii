<?php

namespace Bonzaii;

$app->get('/admin/ajax/{the_works}', function ($the_works) use ($app) {

#  if (!$app['request']->isXmlHttpRequest()) {
#    throw new Exception('Not a XmlHttpRequest');
#  }

  // FIXME: this needs to be validated!
  $path = explode('/', $the_works);
  $class = array_shift($path);
  $method = isset($path[0]) ? array_shift($path) : 'ajax';

  if (isset($app[$class]) && method_exists($app[$class], $method)) {
    if (method_exists($app[$class], 'setParameters')) {
      $app[$class]->setParameters(array('path' => $path));
    }
    $result = $app[$class]->$method();
  }

})->assert('the_works', '.*');
