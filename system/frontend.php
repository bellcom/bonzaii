<?php

// frontpage
$app->get('/', function() use ($app){
  $template = $app['twig']->loadTemplate('index.html.twig');
  return $template->render(array(
    'title' => 'mean aps',
    'swag_line' => 'home is where the heart is',
    'tweets' => $app['tweets']->getList(),
    'articles' => $app['articles']->getList(10),
    'highlight' => $app['articles']->getListByTag('highlight'),
  ));
});

$app->get('/hvem', function() use ($app){
  $template = $app['twig']->loadTemplate('page.html.twig');
  $article = $app['articles']->get('hvem');

  return $template->render(array(
    'title' => 'hvem er vi så',
    'swag_line' => "- top of the world ma'",
    'tweets' => $app['tweets']->getList(),
    'article' => $article
  ));
});

$app->get('/hvad', function() use ($app){
  $template = $app['twig']->loadTemplate('page.html.twig');
  $article = $app['articles']->get('hvad');

  return $template->render(array(
    'title' => 'hvad vi laver',
    'swag_line' => "for det sker faktisk - og ikke så sjældent endda",
    'tweets' => $app['tweets']->getList(),
    'article' => $article
  ));
});

$app->get('/hvor', function() use ($app){
  $template = $app['twig']->loadTemplate('contact.html.twig');
  return $template->render(array(
    'title' => 'hvordan vi kontaktes',
    'swag_line' => '... e.t. phone home ...',
    'employees' => $app['authors']->get()
  ));
});
$app->post('/hvor', function() use ($app){
  // TODO: validation

  // silly little anti robot thingy
  if ('' != $app['request']->get('qemu')) {
    return $app->redirect('/');
  }

  require_once __DIR__.'/vendor/swiftmailer/lib/swift_required.php';
  $msg = $app['request']->get('name') . ' <' . $app['request']->get('email') . ">\n";
  if ($app['request']->get('company')) {
    $msg .= "firma: " . $app['request']->get('company') . "\n";
  }
  $msg .= "\nBesked:\n" . $app['request']->get('message') . "\n\n";
  $msg .= "-- \nmvh mailsystemet!";

  $mailer = $app['mailer'];
  $message = \Swift_Message::newInstance($app['request']->get('subject', 'forespørgsel fra hjemmesiden'))
    ->setFrom($app['request']->get('email'))
    ->setTo(array('ulrik@lazy.dk'))
    ->setBody($msg)
  ;
  $result = $mailer->send($message);

  $app['session']->setFlash('message', 'Tak for din henvendelse - vi vil følge op på den hurtigst muligt.');
  return $app->redirect('/');
});

$app->get('/articles/tagged/{tag}', function($tag) use ($app) {
  $template = $app['twig']->loadTemplate('index.html.twig');
  return $template->render(array(
    'title' => 'tagged "' . $tag . '"',
    'swag_line' => 'divide et impera',
    'tweets' => $app['tweets']->getList(),
    'articles' => $app['articles']->getListByTag($tag)
  ));
});


$app->get('/article/{slug}', function($slug) use ($app){
  $template = $app['twig']->loadTemplate('article.html.twig');

  $article = $app['articles']->get($slug);
  if (!$article) {
    return $app->redirect('/');
  }

  $author = array_shift(explode(' ', $article['author']));

  return $template->render(array(
    'title' => $author . ' fortæller..',
    'swag_line' => '',
    'article' => $article,
    'related' => $app['articles']->related($article['author_id']),
    'highlight' => $app['articles']->getListByTag('highlight'),
  ));
});


$app->post('/api', function() use ($app){
  return "comming soon...";
});

