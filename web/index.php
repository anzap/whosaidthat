<?php

require_once __DIR__.'/../vendors/autoload.php';

use WhoSaidThat\AppInfo;

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

$app = require __DIR__.'/../src/app.php';

$app->run();

