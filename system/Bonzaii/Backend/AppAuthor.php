<?php
/**
 * Author app handler.
 * @package bonzaii
 */


$app->get('/admin/author', function () use ($app) {
  $template = $app['twig']->loadTemplate('backend/author/list.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » author list',
    'authors' => $app['authors']->get()
  ));
});


/**
 * handle creation of new authors
 * view the add page along with the list of authors
 *
 * @return string html
 */
$app->get('/admin/author/add', function () use ($app) {
  $template = $app['twig']->loadTemplate('backend/author/form.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » add new author',
  ));
});

/**
 * load editing form or information
 * the method returns either json data or html
 *
 * @param int $author_id - from GET request
 * @return mixed
 */
$app->get('/admin/author/edit/{author_id}', function ($author_id) use ($app) {

  $author = $app['authors']->get(array('id' => $author_id), true);

  if ($app['request']->isXmlHttpRequest()) {
    return new Response(
      json_encode(array(
        'status' => ($author ? true : false),
        'data' => $author,
        'callback' => 'editAuthor'
      )),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  $template = $app['twig']->loadTemplate('backend/author/form.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » edit author',
    'author' => $author
  ));
});

/**
 * handles post requests
 */

/**
 * handles updates or inserts
 *
 */
$app->post('/admin/author/add', function () use ($app) {
  if ($app['request']->get('id', false)) {
    $result = $app['authors']->update();
    $message = $result ? 'Author updated' : 'Something went wrong in the update.';
  }
  else {
    $result = $app['authors']->add($app['request']);
    $message = $result ? 'Author added' : 'Author already esists.';
  }

  $app['session']->setFlash('message', $message);
  return $app->redirect('/admin/author');
});


$app->post('/admin/author/delete/{author_id}', function ($author_id) use ($app) {
  $result = false;
  if ($author_id) {
    $result = $app['authors']->doDelete(array(
      'id' => $author_id
    ));
  }

  if ($app['request']->isXmlHttpRequest()) {
    return new Response(
      json_encode(array(
        'status' => ($result ? true : false),
        'callback' => 'deleteAuthor',
        'message' => ($result ? 'Author deleted' : 'Author could not be deleted')
      )),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  $app['session']->setFlash('message', 'Author deleted');
  return $app->redirect('/admin/author/add');
});
