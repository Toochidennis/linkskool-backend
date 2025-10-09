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

    public function get($route, $handler)
    {
        $this->addRoute('GET', $route, $handler);
    }

    public function post($route, $handler)
    {
        $this->addRoute('POST', $route, $handler);
    }

    public function put($route, $handler)
    {
        $this->addRoute('PUT', $route, $handler);
    }

    public function delete($route, $handler)
    {
        $this->addRoute('DELETE', $route, $handler);
    }

    public function patch($route, $handler)
    {
        $this->addRoute('PATCH', $route, $handler);
    }
}
