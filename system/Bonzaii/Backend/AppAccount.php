<?php


$app->get('/admin/account/login', function () use ($app) {
  return $app['twig']->render('backend/login.html.twig', array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » login',
    'no_nav' => true
  ));
});

$app->post('/admin/account/login', function () use ($app) {
  if ($app['access']->login()) {
    return $app->redirect('/admin/article');
  }

  $app['session']->setFlash('message', 'access denied...');
  return $app->redirect('/admin/account/login');
});
