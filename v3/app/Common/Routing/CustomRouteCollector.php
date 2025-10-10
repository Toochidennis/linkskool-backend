<?php

namespace V3\App\Common\Routing;

use FastRoute\RouteCollector;

class CustomRouteCollector extends RouteCollector
{
    public array $middlewares = [];

    public function addRoute($method, $route, $handler, $middleware = [])
    {
        parent::addRoute($method, $route, $handler);
        $this->middlewares[$route] = $middleware;
    }

    public function getMiddlewares(string $route): array
    {
        return $this->middlewares[$route] ?? [];
    }
}
