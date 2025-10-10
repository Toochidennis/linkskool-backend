<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use FastRoute\Dispatcher;
use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Middleware\MiddlewareExecutor;
use V3\App\Common\Utilities\{HttpStatus, ResponseHandler};
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use V3\App\Common\Routing\{CustomRouteCollector, AutoRouteRegistrar, BirthdayMessenger};

// Load environment variables
EnvLoader::load();
// BirthdayMessenger::send();
// die;

$response = ['success' => false];


$collector =  new CustomRouteCollector(
    new RouteParser(),
    new GroupCountBased()
);

// Auto-register all controllers
$auto = new AutoRouteRegistrar($collector);
$auto->registerControllers('V3\App\Controllers\Portal', '/portal');
//$auto->registerControllers('V3\App\Controllers\Learning', '/learning');

// Compile routes into a dispatcher
$dispatcher = new GroupCountBasedDispatcher($collector->getData());


$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');

$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$base   = rtrim(dirname(dirname($script)), '/'); // ex: /api3/v3

if ($base && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base)); // drop /api3/v3
}

// normalize
if ($uri === '' || $uri[0] !== '/') {
    $uri = "/$uri";
}

$uri = rawurldecode(preg_replace('#/+#', '/', $uri));
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

//var_dump($routeInfo);

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        $response['message'] = 'Route not found.';
        http_response_code(HttpStatus::NOT_FOUND);
        ResponseHandler::sendJsonResponse($response);
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        $response['message'] = 'Method not allowed.';
        http_response_code(HttpStatus::METHOD_NOT_ALLOWED);
        ResponseHandler::sendJsonResponse($response);
        break;

    case Dispatcher::FOUND:
        [$class, $method] = $routeInfo[1];
        $vars = $routeInfo[2];

        $queryParams = $_GET ?? [];
        $vars = array_merge($vars, $queryParams);

        var_dump($vars);
        die;

        $middlewares = $collector->getMiddlewares($routeInfo[1][1] ?? $uri) ?? [];
        MiddlewareExecutor::run($middlewares);

        $controller = new $class();
        $result = $controller->$method($vars);

        if ($result !== null) {
            http_response_code(HttpStatus::SERVICE_UNAVAILABLE);
            ResponseHandler::sendJsonResponse($result);
        }
        break;
}
