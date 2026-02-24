<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-KEY");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

V3\App\Common\Utilities\EnvLoader::load();
V3\App\Common\Utilities\Logger::init();
V3\App\Common\Events\Registrars\RegisterEventListeners::register();

V3\App\Common\Routing\RouteDispatcher::handle();
