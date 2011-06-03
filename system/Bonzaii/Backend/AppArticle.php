<?php


$app->get('/admin/article', function () use ($app) {
  $template = $app['twig']->loadTemplate('backend/article/list.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » article list',
    'articles' => $app['articles']->getList(0, null),
  ));
});


$app->get('/admin/article/add', function () use ($app) {
  $template = $app['twig']->loadTemplate('backend/article/form.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » add new article',
    'maxsize' => ini_get('post_max_size'),
    'authors' => $app['authors']->get(),
  ));
});

$app->post('/admin/article/add', function () use ($app) {
  if ($app['request']->get('id', false)) {
    $result = $app['articles']->update();
    $message = $result ? 'Article updated' : 'Something went wrong in the update.';
  }
  else {
    $result = $app['articles']->add();
    $message = $result ? 'Article added' : 'Article already esists.';
  }

  $app['session']->setFlash('message', $message);
  return $app->redirect('/admin/article');
});

$app->get('/admin/article/edit/{article_id}', function ($article_id) use ($app) {
  $article = $app['articles']->get($article_id, false);

  $tags = array();
  foreach ($article['tags'] as $tag) {
    $tags[] = $tag['name'];
  }
  $article['tags'] = $tags;

  if ($app['request']->isXmlHttpRequest()) {
    return new Response(
      json_encode(array(
        'status' => ($article ? true : false),
        'data' => $article,
        'callback' => 'editArticle'
      )),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  $template = $app['twig']->loadTemplate('backend/article/form.html.twig');
  return $template->render(array(
    'title' => 'bonzaii',
    'swag_line' => 'ze admin section » edit article',
    'article' => $article,
    'authors' => $app['authors']->get(),
  ));
});


$app->post('/admin/article/delete/{article_id}', function ($article_id) use ($app) {
  $result = false;
  if ($article_id) {
    $result = $app['articles']->doDelete(array(
      'id' => $article_id
    ));
  }

  if ($app['request']->isXmlHttpRequest()) {
    return new Response(
      json_encode(array(
        'status' => ($result ? true : false),
        'callback' => 'deleteArticle',
        'message' => ($result ? 'Article deleted' : 'Article could not be deleted')
      )),
      200,
      array('Content-Type' => 'application/json')
    );
  }

  $app['session']->setFlash('message', 'Article deleted');
  return $app->redirect('/admin/article');
});
