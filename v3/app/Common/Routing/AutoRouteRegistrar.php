<?php

namespace V3\App\Common\Routing;

use ReflectionClass;
use ReflectionMethod;

class AutoRouteRegistrar
{
    private string $cacheFile;

    public function __construct(private CustomRouteCollector $r)
    {
    }

    public function registerControllers(string $namespace, string $prefix = '', bool $useCache = true): void
    {
        if ($useCache && file_exists($this->cacheFile)) {
            $routes = include $this->cacheFile;
            foreach ($routes as $route) {
                $this->r->addRoute(
                    $route['method'],
                    $route['path'],
                    [$route['class'], $route['methodName']],
                    $route['middleware']
                );
            }
            return;
        }

        $path = __DIR__ . '/../../../Controllers/' . str_replace('\\', '/', basename($namespace));

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), 'Controller.php')) {
                continue;
            }

            $className = $namespace . '\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                substr($file->getPathname(), strlen($path) + 1)
            );

            if (!class_exists($className)) {
                continue;
            }

            $ref = new ReflectionClass($className);
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes(Route::class) as $attr) {
                    $route = $attr->newInstance();
                    $uri = rtrim($prefix, '/') . $route->path;

                    $this->r->addRoute(
                        strtoupper($route->method),
                        $uri,
                        [$className, $method->getName()],
                        $route->middleware
                    );
                }
            }
        }
    }

    private function storeCache(array $routes): void
    {
        $content = '<?php return ' . var_export($routes, true) . ';';
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->cacheFile, $content);
    }

    public function clearCache(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }
}
