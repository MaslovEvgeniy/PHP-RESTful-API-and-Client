<?php
error_reporting (E_ALL);

session_start();

define('ROOT', dirname(__FILE__));
define('URL', '/maslov/rest/client');//provide your path here

define('REST', 'http://codeit.pro/maslov/rest/service');//provide link to service here

require_once(ROOT . '/vendor/autoload.php');

$router = new app\components\Router;
$router->run();