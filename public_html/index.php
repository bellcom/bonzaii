<?php

// catch "old bellcom" urls
if (substr($_SERVER['REQUEST_URI'], 1,1) == '?') {
  $_SERVER['REQUEST_URI'] = str_replace('?', '', $_SERVER['REQUEST_URI']);
}

$app = require __DIR__ . '/../system/app.php';
$app->run();

