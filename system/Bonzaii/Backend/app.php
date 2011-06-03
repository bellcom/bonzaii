<?php
/**
 * delegate calls to "modules"
 */

namespace Bonzaii;

$path = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 4);
$path = __DIR__ .'/App'. ucfirst(strtolower($path[2])) . '.php';
if (!is_file($path)) {
  throw new \Exception('File not found: ' . $path);
}
require $path;
