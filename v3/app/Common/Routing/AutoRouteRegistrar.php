<?php
namespace V3\App\Common\Routing;

use ReflectionClass;
use ReflectionMethod;

class AutoRouteRegistrar
{
    public function __construct(private CustomRouteCollector $r) {}

    public function registerControllers(string $namespace, string $prefix = ''): void
    {
        $path = __DIR__ . '/../../../Controllers/' . str_replace('\\', '/', basename($namespace));

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), 'Controller.php')) continue;

            $className = $namespace . '\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                substr($file->getPathname(), strlen($path) + 1)
            );

            if (!class_exists($className)) continue;

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
}
