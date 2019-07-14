<?php
date_default_timezone_set('Europe/Moscow');

dirname(__DIR__);

require 'init_autoloader.php';

try {
    $app = \Application\Application::init();
    $app->handleRequest();
} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo htmlspecialchars($e);
}
