<?php
/**
 * FRONT CONTROLLER – mọi request đều đi qua file này.
 * (Apache: public/.htaccess chuyển hướng về đây; php -S: tự động rơi về đây.)
 */
declare(strict_types=1);

define('PUBLIC_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

require APP_PATH . '/core/bootstrap.php';

$router = require APP_PATH . '/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', request_path());
