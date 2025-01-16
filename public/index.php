<?php
require_once __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use V3\App\Utilities\ResponseHandler;

$response = ['success' => false, 'message' => ''];

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('POST', '/auth/login', ['AuthController', 'handleAuthRequest']);
    $r->addRoute('POST', '/auth/logout', ['AuthController', 'logout']);
});

// Fetch method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) from URI
if (false !== $pos = strpos($uri, '?')) {
    $uri  = substr($uri, 0, $pos);
}
$uri = rawurldecode($url);


// Dispatch the route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        $response['message'] = 'Route not found.';
        ResponseHandler::sendJsonResponse($response);
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        $response['message'] = 'Method not allowed.';
        ResponseHandler::sendJsonResponse($response);
        break;

    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        // Split the handler (e.g [AuthController, handleAuthRequest])
        [$class, $method] = explode(', ', $handler);

        // Instantiate and call the method
        $controller = "V3\App\Controllers\$class";
        if (class_exists($controller) && method_exists($class, $method)) {
            (new $controller())->$method($vars);
        } else {
            http_response_code(500);
            $response['message'] = 'Invalid handler';
            ResponseHandler::sendJsonResponse($response);
        }
        break;
}
