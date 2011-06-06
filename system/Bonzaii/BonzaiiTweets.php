<?php

namespace Bonzaii;
use Silex\Application;

class BonzaiiTweets {
  protected $app;
  public function __construct(Application $app) {
    $this->app = $app;
    $this->cache = $this->app['tweets.cache_dir'] ? : false;
  }

  public function getList($limit = NULL, $search = NULL) {

    // limit from settings
    $limit = $limit ? : $this->app['tweets.limit'];
    $search = $search ? : $this->app['tweets.search'];

    $cache = false;
    $tweets = false;
    if ($this->cache) {
      $cache = $this->cache . md5($search.$limit) . '.php';
      if (is_file($cache) && (filemtime($cache) > (time() - 3600))) {
        $tweets = include $cache;
      }
    }

    if (!$tweets) {
      // load latest news from "blog" and bellcomdk's twitterfeed.
      $result = file_get_contents('https://api.twitter.com/1/statuses/user_timeline.json?screen_name=' . urlencode($search) . '&count=' . $limit);
      if ($result) {
        $tweets = array();
        $records = json_decode($result);
        foreach ($records as $i => $tweet) {
          $text = $tweet->text;

          // parse urls
          $text = preg_replace_callback('~((http|https|ftp)://[^\s]+)~i', function($matches) {
              return '<a href="'.$matches[0].'" title="gå til hjemmesiden" rel="external">' . $matches[0] . '</a>';
          }, $text);

          // parse twitter tags
          $text = preg_replace_callback('~[#|@][(\w|æ|ø|å)]*~i', function($matches) {
            if ($matches[0] == '#') {
              $url = '#!/' . urlencode($matches[0]);
            }
            else {
              $url = $matches[0];
            }
            return '<a href="https://twitter.com/' . $url . '" title="se mere på twitter" rel="twitter">' . $matches[0] . '</a>';
          }, $text);

          $tweets[] = array(
            'created_at' => date('d.m.Y', strtotime($tweet->created_at)),
            'text' => $text
          );
        }
      }
    }

    if ($cache) {
      file_put_contents($cache, "<?php\nreturn " . var_export($tweets, true) . ";\n");
    }

    return $tweets;
  }
}
