<?php

namespace Bonzaii;
use Silex\Application;

class BonzaiiRedirects {
  protected $app;
  public function __construct(Application $app) {
    $this->app = $app;
  }

  public function get($id) {
    return $this->app['db']->fetchAssoc("SELECT * from redirects WHERE id = ?", array($id));
  }

  public function find($from) {
    $to = $this->app['db']->fetchAssoc("SELECT * FROM redirects WHERE from_url = ?", array($from));

    if (empty($to['to_article_id'])) {
      return $to['to_url'];
    }

    $article = $this->app['articles']->get((int) $to['to_article_id']);
    if ($article) {
      return '/' . $article['type'] . '/' . $article['slug'];
    }

    return false;
  }


  public function getList() {
    $query = "
      SELECT *
      FROM redirects
    ";
    return $this->app['db']->fetchAll($query);
  }

  public function add() {
    $request = $this->app['request'];

    $aid = '';
    if ($request->get('to_article_id')) {
      $aid = (int) $request->get('to_article_id');
    }

    // FIXME: exists check...
    return $this->app['db']->insert('redirects', array(
      'from_url' => $request->get('from_url'),
      'to_url' => $request->get('to_url'),
      'to_article_id' => $aid,
    ));
  }

  public function update() {
    $request = $this->app['request'];
    if (false == $request->get('id', false)) {
      return false;
    }
    $id = $request->get('id');

    $aid = '';
    if ($request->get('to_article_id')) {
      $aid = (int) $request->get('to_article_id');
    }

    // FIXME: exists check...
    return $this->app['db']->update('redirects', array(
      'from_url' => $request->get('from_url'),
      'to_url' => $request->get('to_url'),
      'to_article_id' => $aid,
    ), array('id' => $id));
  }

  public function doDelete(array $criteria = array()) {
    return $this->app['db']->delete('redirects', $criteria);
  }
}
