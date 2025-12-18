<?php

namespace V3\App\Common\Routing;

use FastRoute\RouteCollector;

class CustomRouteCollector extends RouteCollector
{
    private array $middlewares = [];

    public function addRoute($method, $route, $handler, $middleware = []): void
    {
        parent::addRoute($method, $route, $handler);

        // Generate a consistent middleware key (Class@method)
        $key = (is_array($handler)) ? "$handler[0]@$handler[1]" : (string) $handler;

        $this->middlewares[$key] = $middleware;
    }

    public function getMiddlewares(string $key): array
    {
        return $this->middlewares[$key] ?? [];
    }

    // for debugging
    public function allMiddlewares(): array
    {
        return $this->middlewares;
    }
}
