<?php

$rootPath = getenv('IS_TRAVIS') ? __DIR_.'/../' : __DIR__.'/../../../../';

// Require Craft unit test bootstrap
require_once $rootPath.'/craft/app/tests/bootstrap.php';

// Require autoloader
require_once $rootPath.'/vendor/autoload.php';
