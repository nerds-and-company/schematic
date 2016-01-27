<?php

$rootPath = getenv('TRAVIS') ? __DIR__.'/../' : __DIR__.'/../../../../';

// Require Craft unit test bootstrap
require_once $rootPath.'craft/app/tests/bootstrap.php';
require_once CRAFT_APP_PATH.'Info.php';

// Require autoloader
require_once $rootPath.'vendor/autoload.php';
