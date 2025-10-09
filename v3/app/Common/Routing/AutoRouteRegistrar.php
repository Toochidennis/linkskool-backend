<?php

namespace V3\App\Common\Routing;

use ReflectionClass;
use ReflectionMethod;

class AutoRouteRegistrar
{
    public function __construct(private CustomRouteCollector $r)
    {
    }

    private function getCacheFile(string $prefix): string
    {
        // sanitize prefix (/portal → portal)
        $safePrefix = trim(str_replace('/', '_', $prefix), '_');
        return __DIR__ . "/../../public/storage/routes.{$safePrefix}.cache.php";
    }

    public function registerControllers(string $baseNamespace, string $basePrefix = '', bool $useCache = true): void
    {
        $cacheFile = $this->getCacheFile($basePrefix);

        // Load from cache if exists
        if ($useCache && file_exists($cacheFile)) {
            $routes = include $cacheFile;
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

        // Scan controllers recursively
        $routes = [];
        $controllerPath = __DIR__ . '/../../Controllers/' . str_replace('\\', '/', basename($baseNamespace));
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($controllerPath));

        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), 'Controller.php')) {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($controllerPath) + 1);
            $className = $baseNamespace . '\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (!class_exists($className)) {
                continue;
            }
            $refClass = new ReflectionClass($className);

            $groupAttr = $refClass->getAttributes(Group::class);
            if (!empty($groupAttr)) {
                $prefix = $groupAttr[0]->newInstance()->prefix;
            } else {
                $relativeNs = str_replace($baseNamespace, '', $refClass->getNamespaceName());
                $segments = array_filter(explode('\\', $relativeNs));
                $prefixParts = array_map(fn($s) => strtolower($s), $segments);
                $prefix = rtrim($basePrefix, '/') . '/' . implode('/', $prefixParts);
            }

            foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes(Route::class) as $attr) {
                    $route = $attr->newInstance();
                    $fullPath = rtrim($prefix, '/') . $route->path;

                    $data = [
                        'method'     => strtoupper($route->method),
                        'path'       => $fullPath,
                        'class'      => $className,
                        'methodName' => $method->getName(),
                        'middleware' => $route->middleware,
                    ];

                    $routes[] = $data;

                    $this->r->addRoute(
                        $data['method'],
                        $data['path'],
                        [$data['class'], $data['methodName']],
                        $data['middleware']
                    );
                }
            }
        }

        if ($useCache) {
            $this->storeCache($routes, $cacheFile);
        }
    }

    private function storeCache(array $routes, string $cacheFile): void
    {
        $content = '<?php return ' . var_export($routes, true) . ';';
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($cacheFile, $content);
    }

    public function clearCache(?string $prefix = null): void
    {
        $storageDir = __DIR__ . '/../../public/storage/';
        if ($prefix) {
            $safePrefix = trim(str_replace('/', '_', $prefix), '_');
            $file = "{$storageDir}routes.{$safePrefix}.cache.php";
            if (file_exists($file)) {
                unlink($file);
            }
        } else {
            foreach (glob("{$storageDir}routes.*.cache.php") as $file) {
                unlink($file);
            }
        }
    }
}
