<?php
/**
 * Tag app handler.
 * @package bonzaii
 */

$app->get('/admin/tag', function () use ($app) {
  $template = $app['twig']->loadTemplate('backend/tag/list.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » tag list',
    'tags' => $app['tags']->get()
  ));
});

$app->get('/admin/tag/edit/{tag_id}', function ($tag_id) use ($app) {
  $tag = $app['tags']->get(array('id' => $tag_id), true);

  if ($app['request']->isXmlHttpRequest()) {
    return new Response(
      json_encode(array(
        'status' => ($tag ? true : false),
        'data' => $tag,
        'callback' => 'editTag'
      )),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  $template = $app['twig']->loadTemplate('backend/tag/form.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » edit tag',
    'tag' => $tag
  ));
});

$app->post('/admin/tag/add', function () use ($app) {
  if ($app['request']->get('id', false)) {
    $result = $app['tags']->update();
    $message = $result ? 'Tag updated' : 'Something went wrong in the update.';
  }
  else {
    $result = $app['tags']->add($app['request']);
    $message = $result ? 'Tag added' : 'Tagalready esists.';
  }

  $app['session']->setFlash('message', $message);
  return $app->redirect('/admin/tag');
});


$app->post('/admin/tag/delete/{tag_id}', function ($tag_id) use ($app) {
  $result = false;
  if ($tag_id) {
    $result = $app['tags']->doDelete(array(
      'id' => $tag_id
    ));
  }

  if ($app['request']->isXmlHttpRequest()) {
    return new Response(
      json_encode(array(
        'status' => ($result ? true : false),
        'callback' => 'deleteTag',
        'message' => ($result ? 'Tag deleted' : 'The tag could not be deleted')
      )),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  $app['session']->setFlash('message', 'Author deleted');
  return $app->redirect('/admin/tag');
});
