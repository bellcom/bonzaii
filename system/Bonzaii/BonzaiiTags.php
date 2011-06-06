<?php

namespace Bonzaii;
use Silex\Application;

class BonzaiiTags {
  protected $app;
  public function __construct(Application $app) {
    $this->app = $app;
  }

  public function get(array $criteria = array(), $limit = NULL) {

    $where = array();
    $limit = '';

    foreach ($criteria as $key => $value) {
      $where[] = $key . " = '" . $this->app['db']->quote($value) . "'";
    }

    if (count($where)) {
      $where = 'WHERE ' . implode(' AND ', $where);
    }

    if ($limit) {
      $limit = 'LIMIT ' . $limit;
    }

    $query = "
      SELECT
        tags.*,
        COUNT(tags_2_articles.tag_id) as article_count
      FROM
        tags
      LEFT JOIN
        tags_2_articles
        ON
          (tags.id = tags_2_articles.tag_id)
      GROUP BY tags_2_articles.tag_id
      ORDER BY tags.name
    ";

    $tags = $this->app['db']->fetchAll($query);
    return $tags ? : array();
  }

  public function doDelete(array $criteria = array()) {
    if (isset($criteria['id'])) {
      $this->app['db']->delete('tags_2_articles', array(
        'tag_id' => (int) $criteria['id'],
      ));
    }
    return $this->app['db']->delete('tags', $criteria);
  }

  public function update() {
    $data = array(
      'name' => $this->app['db']->quote(trim($this->app['request']->get('name')), \PDO::PARAM_STR),
    );

    return $this->app['db']->update('authors', $data, array(
      'id' => (int) $this->app['request']->get('id')
    ));
  }
}
