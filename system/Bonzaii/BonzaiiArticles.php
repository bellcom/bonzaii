<?php

namespace Bonzaii;
use Silex\Application;

class BonzaiiArticles {
  protected $app;

  public function __construct(Application $app) {
    $this->app = $app;
  }

  public function add() {
    $request = $this->app['request'];

    // FIXME: exists check...
    $this->app['db']->insert('articles', array(
      'author_id' => $request->get('author_id'),
      'content' => $request->get('content'),
      'manchet' => $request->get('manchet'),
      'created_at' => time(),
      'slug' => self::stripText($request->get('slug', $request->get('title'))),
      'title' => $request->get('title'),
      'type' => $request->get('type'),
    ));
    $id = $this->app['db']->fetchColumn('SELECT last_insert_rowid() AS last_insert_rowid');

    $this->storeImage($id, $request->files->get('image'));
    $this->tagArticle($id, $request->get('tags'));
  }

  public function update() {
    $request = $this->app['request'];

    if (false == $request->get('id', false)) {
      return false;
    }
    $id = $request->get('id');

    $this->app['db']->update('articles', array(
      'author_id' => $request->get('author_id'),
      'content' => $request->get('content'),
      'manchet' => $request->get('manchet'),
      'created_at' => time(),
      'slug' => self::stripText($request->get('slug', $request->get('title'))),
      'title' => $request->get('title'),
      'type' => $request->get('type'),
    ), array('id' => $id));

    $this->storeImage($id, $request->files->get('image'));
    $this->tagArticle($id, $request->get('tags'));

    return true;
  }


  public function getList($limit = NULL, $filter = 'article') {
    // limit from settings
    $limit_addon = '';
    if ($limit != 0) {
      $limit_addon = 'LIMIT ' .  ($limit ? : $this->app['articles.limit']);
    }

     $filter_addon = '';
    if ($filter) {
      $filter_addon = "WHERE articles.type = '{$filter}'";
    }

    $query = "
      SELECT
        articles.id,
        articles.title,
        articles.type,
        articles.slug,
        articles.content,
        articles.manchet,
        articles.created_at,
        authors.id AS author_id,
        authors.name AS author,
        authors.email AS author_email,
        authors.twitter AS author_twitter
      FROM
        articles
      JOIN
        authors
        ON
          (articles.author_id = authors.id)
      $filter_addon
      ORDER BY
        articles.created_at DESC
      {$limit_addon}
    ";

    $records = array();
    foreach ($this->app['db']->fetchAll($query) AS $article) {
      $records[] = $this->extendArticle($article);
    }

    return $records;
  }


  public function getListByTag($tag) {
    $query = "
      SELECT
        articles.id,
        articles.title,
        articles.type,
        articles.slug,
        articles.content,
        articles.manchet,
        articles.created_at,
        authors.id AS author_id,
        authors.name AS author,
        authors.email AS author_email,
        authors.twitter AS author_twitter
      FROM
        articles
      JOIN
        authors
        ON
          (articles.author_id = authors.id)
      JOIN
        tags_2_articles AS t2a
        ON
          (t2a.article_id = articles.id)
      JOIN
        tags
        ON
          (tags.id = t2a.tag_id)
      WHERE
        articles.type = 'article'
        AND
          tags.name = ?
      ORDER BY
        articles.created_at DESC
    ";

    $records = array();
    foreach ($this->app['db']->fetchAll($query, array($tag)) AS $article) {
      $records[] = $this->extendArticle($article);
    }

    return $records;
  }

  /**
   * delete an article record.
   *
   * @param array $criteria
   * @return mixed
   */
  public function doDelete(array $criteria = array()) {
    if (isset($criteria['id'])) {
      foreach (glob($this->app['articles.image_path'] . $criteria['id'] . '.*') as $file) {
        unlink($file);
      }

      $this->app['db']->delete('tags_2_articles', array('article_id' => $criteria['id']));
    }

    return $this->app['db']->delete('articles', $criteria);
  }

  public function get($slug) {

    $table = 'slug';
    if (is_numeric($slug)) {
      $table = 'id';
    }

    $query = "
      SELECT
        articles.id,
        articles.title,
        articles.slug,
        articles.content,
        articles.manchet,
        articles.type,
        articles.created_at,
        authors.id AS author_id,
        authors.name AS author,
        authors.email AS author_email,
        authors.twitter AS author_twitter
      FROM
        articles
      JOIN
        authors
        ON
          (articles.author_id = authors.id)
      WHERE
        articles.".$table." = ?
    ";

    $article = $this->app['db']->fetchAssoc($query, array($slug));
    return $article ? $this->extendArticle($article) : array();
  }

  public function related($id) {
    $query = "
      SELECT
        articles.id,
        articles.title,
        articles.slug,
        articles.manchet,
        articles.content,
        articles.type,
        articles.created_at,
        authors.id AS author_id,
        authors.name AS author,
        authors.email AS author_email,
        authors.twitter AS author_twitter
      FROM
        articles
      JOIN
        authors
        ON
          (articles.author_id = authors.id)
      WHERE
        articles.type = 'article'
        AND
          authors.id = ?
      ORDER BY
        articles.created_at DESC
      LIMIT 10
    ";

    $articles = $this->app['db']->fetchAll($query, array($id));
    foreach ($articles as $k => $article) {
      $articles[$k] = $this->extendArticle($article);
    }

    return $articles;
  }


  public static function stripText($v) {
    $url_safe_char_map = array(
      'æ' => 'ae', 'Æ' => 'AE',
      'ø' => 'oe', 'Ø' => 'OE',
      'å' => 'aa', 'Å' => 'AA',
      'é' => 'e',  'É' => 'E', 'è' => 'e', 'È' => 'E',
      'à' => 'a',  'À' => 'A', 'ä' => 'a', 'Ä' => 'A', 'ã' => 'a', 'Ã' => 'A',
      'ò' => 'o',  'Ò' => 'O', 'ö' => 'o', 'Ö' => 'O', 'õ' => 'o', 'Õ' => 'O',
      'ù' => 'u',  'Ù' => 'U', 'ú' => 'u', 'Ú' => 'U', 'ũ' => 'u', 'Ũ' => 'U',
      'ì' => 'i',  'Ì' => 'I', 'í' => 'i', 'Í' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I',
      'ß' => 'ss',
      'ý' => 'y', 'Ý' => 'Y',
      ' ' => '-',
    );

    $search  = array_keys($url_safe_char_map);
    $replace = array_values($url_safe_char_map);

    $v = str_replace(' ', '-', trim($v));
    $v = str_replace($search, $replace, $v);

    $v = preg_replace('/[^a-z0-9_-]+/i', '', $v);
    $v = preg_replace('/[-]+/', '-', $v);
    $v = preg_replace('/^-|-$/', '-', $v);

    return strtolower($v);
  }

  protected function tagArticle($aid, $tags) {
    $tags = explode(',', $tags);
    array_walk($tags, function(&$n) {
      $n = trim($n);
    });

    foreach ($tags as $tag) {
      $tid = $this->app['db']->fetchColumn("SELECT id FROM tags WHERE name = ?", array($tag), 0);
      if (empty($tid)) {
        $this->app['db']->insert('tags', array('name' => $tag));
        $tid = $this->app['db']->fetchColumn('SELECT last_insert_rowid() AS last_insert_rowid');
      }

      $a = $this->app['db']->fetchColumn("SELECT COUNT(*) FROM tags_2_articles WHERE article_id = ? AND tag_id = ?", array($aid, $tid), 0);
      if ($a == 0) {
        $this->app['db']->insert('tags_2_articles', array(
          'article_id' => $aid,
          'tag_id' => $tid,
        ));
      }
    }
  }

  protected function storeImage($aid, $file) {
    $extentions = array(
      'image/jpeg',
      'image/png',
      'image/gif',
    );

    if ($file && $file->isValid() && in_array($file->getMimeType(), $extentions)) {
      $file->move($this->app['articles.image_upload_path'], $aid . $file->getExtension());
    }
  }


  protected static function mb_substrws($text, $length = 180) {
    if((mb_strlen($text) > $length)) {
      $whitespaceposition = mb_strpos($text, ' ', $length) - 1;
      if($whitespaceposition > 0) {
        $chars = count_chars(mb_substr($text, 0, ($whitespaceposition + 1)), 1);
        if ($chars[ord('<')] > $chars[ord('>')]) {
          $whitespaceposition = mb_strpos($text, ">", $whitespaceposition) - 1;
        }
        $text = mb_substr($text, 0, ($whitespaceposition + 1));
      }
      // close unclosed html tags
      if(preg_match_all("|(<([\w]+)[^>]*>)|", $text, $aBuffer)) {
        if(!empty($aBuffer[1])) {
          preg_match_all("|</([a-zA-Z]+)>|", $text, $aBuffer2);
          if(count($aBuffer[2]) != count($aBuffer2[1])) {
            $closing_tags = array_diff($aBuffer[2], $aBuffer2[1]);
            $closing_tags = array_reverse($closing_tags);
            foreach($closing_tags as $tag) {
              $text .= '</'.$tag.'>';
            }
          }
        }
      }
    }
    return $text;
  }

  protected function extendArticle($article) {
    $article['datetime'] = date('Y-m-d H:i', $article['created_at']);
    $article['date'] = date('d.m.Y', $article['created_at']);

    $teaser = self::mb_substrws($article['content'], 1000);

    $article['teaser'] = $teaser . ($teaser == $article['content'] ? '' : (' ... <a href="/article/' . $article['slug'] . '">Læs mere</a> '));

    $query = "
      SELECT
        tags.name
      FROM
        tags
      JOIN
        tags_2_articles
        ON
          (tags.id = tags_2_articles.tag_id)
      WHERE
        tags_2_articles.article_id = ?
    ";
    $article['tags'] = $this->app['db']->fetchAll($query, array($article['id']));

    $files = glob($this->app['articles.image_upload_path'] . $article['id'] . '.{jpg,gif,png}', GLOB_BRACE);
    if ($files) {
      foreach ($files as $file) {
        $article['image'] = $this->app['articles.image_path'] . basename($file);
      }
    }

    return $article;
  }
}
