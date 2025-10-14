<?php

namespace V3\App\Common\Routing;

use FastRoute\Dispatcher;
use FastRoute\RouteParser\Std as RouteParser;
use V3\App\Common\Middleware\MiddlewareExecutor;
use FastRoute\DataGenerator\GroupCountBased as DataGen;
use V3\App\Common\Utilities\{HttpStatus, ResponseHandler, EnvLoader};
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;

class RouteDispatcher
{
    public static function handle(): void
    {
        $response = ['success' => false];

        $collector =  new CustomRouteCollector(new RouteParser(), new DataGen());
        $auto = new AutoRouteRegistrar($collector);
        $auto->registerControllers('V3\App\Controllers\Portal', '/portal', false);
        $auto->registerControllers('V3\App\Controllers\Explore', '/public', false);
        //$auto->registerControllers('V3\App\Controllers\Learning', '/learning');

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
                [$controller, $method] = $routeInfo[1];
                $vars = $routeInfo[2];

                // Merge in query parameters (?filter=active)
                $queryParams = $_GET ?? [];
                $vars = array_merge($vars, $queryParams);

                // Build the handler key (same as used in CustomRouteCollector)
                $key = "$controller@$method";

                // Fetch middleware by key
                $middlewares = $collector->getMiddlewares($key);
                MiddlewareExecutor::run($middlewares);

                if (class_exists($controller) && method_exists($controller, $method)) {
                    (new $controller())->$method($vars);
                } else {
                    $response['message'] = 'Invalid handler';
                    http_response_code(HttpStatus::SERVICE_UNAVAILABLE);
                    ResponseHandler::sendJsonResponse($response);
                }
                break;
        }
    }
}
