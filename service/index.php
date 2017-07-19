<?php
error_reporting (E_ALL);

define('ROOT', dirname(__FILE__));
define('PATH', 'maslov/rest/service/');

require_once(ROOT . '/vendor/autoload.php');

$server = new app\components\API;
$server->processRequest();