<?php
define('BASE_PATH', dirname(__DIR__));
require __DIR__.'/../vendor/autoload.php';

App\Core\Session::start();

$router = require BASE_PATH.'/app/Routes/web.php';
$router->dispatch();
