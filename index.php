<?php
ini_set('display_errors', 1);
ini_set('date.timezone', 'Asia/Shanghai');
error_reporting(E_ALL);
session_start();
define('WEB_ROOT', dirname(__FILE__));
define('EXT_PATH', dirname(__FILE__) . "/ext");
define('RESOURCE_PATH', WEB_ROOT . '/resource');
define('COOKIE_JAR', RESOURCE_PATH . '/cookie/' . session_id()); //cookie文件存放位置
define('CERT_PATH', RESOURCE_PATH . '/cert/ca-bundle.crt'); //證書文件存放位置
define('CONFIG_PATH', WEB_ROOT . '/resource/config');
define('TIMESTAMP', time());

require_once WEB_ROOT . "/lib/AutoLoader.php";
\lib\AutoLoader::autoLoad(); //自動加載類spl
$route = new \lib\Route();
$route->run();
exit;
