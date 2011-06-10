<?php

use Symfony\Component\HttpFoundation\Response;

$app->get('/admin/redirect', function () use ($app) {
  $template = $app['twig']->loadTemplate('backend/redirect/list.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » redirect list',
    'redirects' => $app['redirects']->getList(0, null),
  ));
});


$app->get('/admin/redirect/add', function () use ($app) {
  $template = $app['twig']->loadTemplate('backend/redirect/form.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » add new redirect',
    'maxsize' => ini_get('post_max_size'),
    'articles' => $app['articles']->getList(0, ''),
  ));
});

$app->post('/admin/redirect/add', function () use ($app) {
  if ($app['request']->get('id', false)) {
    $result = $app['redirects']->update();
    $message = $result ? 'Redirect updated' : 'Something went wrong in the update.';
  }
  else {
    $result = $app['redirects']->add();
    $message = $result ? 'Redirect added' : 'Redirect already esists.';
  }

  $app['session']->setFlash('message', $message);
  return $app->redirect('/admin/redirect');
});

$app->get('/admin/redirect/edit/{redirect_id}', function ($redirect_id) use ($app) {
  $redirect = $app['redirects']->get($redirect_id, false);

  if ($app['request']->isXmlHttpRequest()) {
    return new Response(
      json_encode(array(
        'status' => ($redirect ? true : false),
        'data' => $redirect,
        'callback' => 'editRedirect'
      )),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  $template = $app['twig']->loadTemplate('backend/redirect/form.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » edit redirect',
    'redirect' => $redirect,
    'articles' => $app['articles']->getList(0, ''),
  ));
});


$app->post('/admin/redirect/delete/{redirect_id}', function ($redirect_id) use ($app) {
  $result = false;
  if ($redirect_id) {
    $result = $app['redirects']->doDelete(array(
      'id' => $redirect_id
    ));
  }

  if ($app['request']->isXmlHttpRequest()) {
    return new Response(
      json_encode(array(
        'status' => ($result ? true : false),
        'callback' => 'deleteRedirect',
        'message' => ($result ? 'Redirect deleted' : 'Redirect could not be deleted')
      )),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  $app['session']->setFlash('message', 'Redirect deleted');
  return $app->redirect('/admin/redirect');
});
