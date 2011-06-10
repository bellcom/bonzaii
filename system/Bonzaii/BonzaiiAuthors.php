<?php

namespace Bonzaii;
use Silex\Application;

class BonzaiiAuthors {
  protected $app;
  protected $params = array();

  public function __construct(Application $app) {
    $this->app = $app;
  }


  public function setParameters(array $params = array()) {
    $this->params = array_merge($this->params, $params);
  }


  /**
   * add a new author
   *
   * @todo error handeling and validation
   *
   * @return result
   */
  public function add() {
    $request = $this->app['request'];

    // check for existing authors
    $check = $this->get(array('email' => $request->get('email')));
    if ($check && count($check)) {
      return false;
    }

    $data = array(
      'name' => $request->get('name'),
      'email' => $request->get('email'),
      'twitter' => $request->get('twitter'),
    );

    if ($request->get('password')) {
      $data['password'] = md5($request->get('password'));
    }
    if ($request->get('content')) {
      $data['content'] = $request->get('content');
    }

    if ($this->app['db']->insert('authors', $data)) {
      $id = $this->app['db']->fetchColumn('SELECT last_insert_rowid() AS last_insert_rowid');

      $this->storeImage($id, $request->files->get('image'));
      return true;
    }

    return false;
  }


  /**
   * update an existing record
   *
   * @todo error handeling and validation
   *
   * @return result
   */
  public function update() {
    $request = $this->app['request'];

    $data = array(
      'name' => $request->get('name'),
      'email' => $request->get('email'),
      'twitter' => $request->get('twitter'),
    );

    if ($request->get('password')) {
      $data['password'] = md5($request->get('password'));
    }
    if ($request->get('content')) {
      $data['content'] = $request->get('content');
    }

    $this->app['db']->update('authors', $data, array(
      'id' => $request->get('id')
    ));
    $this->storeImage($request->get('id'), $request->files->get('image'));

    return true;
  }

    /**
   * get author objects
   *
   * @param array $criteria
   * @param boolean $return_one if set single results will not return an array of objects, only the "real" record
   * @return array
   */
  public function get(array $criteria = array(), $return_one = false) {

    $where = array();
    foreach ($criteria as $key => $value) {
      $where[] = $key . " = '" .$value. "'";
    }

    if (count($where)) {
      $where = 'WHERE ' . implode(' AND ', $where);
    }
    else {
      $where = '';
    }

    $query = "
      SELECT
        id, name, email, twitter
      FROM
        authors
      {$where}
      ORDER BY name
    ";

    $records = $this->app['db']->fetchAll($query);
    if ($return_one) {
      if (count($records)) {
        return array_shift($records);
      }
      return false;
    }

    return $records;
  }


  /**
   * delete an author record.
   *
   * @todo only delete authors without related articles
   *
   * @param array $criteria
   * @return mixed
   */
  public function doDelete(array $criteria = array()) {
    return $this->app['db']->delete('authors', $criteria);
  }

  protected function storeImage($aid, $file) {
    $extentions = array(
      'image/jpeg',
      'image/png',
      'image/gif',
    );

    if ($file && $file->isValid() && in_array($file->getMimeType(), $extentions)) {
      $file->move($this->app['authors.image_upload_path'], 'author_'.$aid . $file->getExtension());
    }
  }

}
