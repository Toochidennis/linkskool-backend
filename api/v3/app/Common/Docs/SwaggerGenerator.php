<?php

namespace V3\App\Common\Docs;

use ReflectionClass;

class SwaggerGenerator
{
    public static function generate(array $cachedRoutes, string $outputPath): void
    {
        $groups = self::groupRoutesByPrefix($cachedRoutes);

        $paths = [];
        foreach ($groups as $groupName => $routes) {
            foreach ($routes as $route) {
                $method = strtolower($route['method']);
                $path = $route['path'];

                [$summary, $description, $queryParams] = self::extractDocs($route['class'], $route['methodName']);
                $pathParams = self::extractPathParameters($path);

                // Detect guards / middlewares
                $guards = $route['middleware'] ?? [];
                $security = self::mapGuardsToSecurity($guards);

                $paths[$path][$method] = [
                    'tags' => [$groupName],
                    'summary' => $summary ?: self::cleanSummary($route['methodName']),
                    'description' => $description,
                    'operationId' => "{$route['class']}::{$route['methodName']}",
                    'parameters' => array_merge($pathParams, $queryParams),
                    'responses' => [
                        '200' => ['description' => 'Successful response'],
                    ],
                    'security' => $security,
                ];
            }
        }

        $openapi = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'LinkSkool API',
                'version' => '1.0.0',
                'description' => 'Auto-generated API documentation with parameter detection (path + query).',
            ],
            'servers' => [
                ['url' => 'https://linkskool.net/api/v3/', 'description' => 'Base server'],
            ],
            'tags' => self::generateTags($groups),
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
            ],
        ];

        file_put_contents($outputPath, json_encode($openapi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private static function groupRoutesByPrefix(array $routes): array
    {
        $groups = [];
        foreach ($routes as $route) {
            preg_match('#^/([^/]+)#', $route['path'], $match);
            $group = $match[1] ?? 'misc';
            $groups[$group][] = $route;
        }
        ksort($groups);
        return $groups;
    }

    private static function generateTags(array $groups): array
    {
        $tags = [];
        foreach (array_keys($groups) as $name) {
            $tags[] = ['name' => $name, 'description' => ucfirst($name) . ' module'];
        }
        return $tags;
    }

    private static function extractDocs(string $className, string $methodName): array
    {
        try {
            $class = new ReflectionClass($className);
            $method = $class->getMethod($methodName);

            $classDoc = $class->getDocComment() ?: '';
            $methodDoc = $method->getDocComment() ?: '';

            $summary = self::parseSummary($methodDoc) ?? self::parseSummary($classDoc);
            $description = self::parseDescription($methodDoc);
            $queryParams = self::extractQueryParameters($methodDoc);

            return [$summary, $description, $queryParams];
        } catch (\ReflectionException $e) {
            return [null, null, []];
        }
    }

    private static function parseSummary(string $doc): ?string
    {
        if (preg_match('/\*\s+([A-Z][^@]+)/', $doc, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private static function parseDescription(string $doc): ?string
    {
        $lines = array_filter(array_map('trim', explode("\n", $doc)));
        $clean = [];
        foreach ($lines as $line) {
            $line = preg_replace('/^\*\s?/', '', $line);
            if (!preg_match('/^(@|\/)/', $line) && strlen($line) > 1) {
                $clean[] = $line;
            }
        }
        return implode("\n", $clean);
    }

    private static function extractPathParameters(string $path): array
    {
        $params = [];
        if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)(:[^}]*)?\}/', $path, $matches)) {
            foreach ($matches[1] as $name) {
                $params[] = [
                    'name' => $name,
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'string'],
                    'description' => "Path parameter '{$name}'",
                ];
            }
        }
        return $params;
    }

    private static function extractQueryParameters(string $doc): array
    {
        $params = [];
        // Matches @queryParam name type description
        if (preg_match_all('/@queryParam\s+(\w+)\s+(\w+)?\s*(.*)?$/mi', $doc, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $params[] = [
                    'name' => $match[1],
                    'in' => 'query',
                    'required' => false,
                    'schema' => ['type' => $match[2] ?: 'string'],
                    'description' => trim($match[3] ?? "Query parameter '{$match[1]}'"),
                ];
            }
        }
        return $params;
    }

    private static function mapGuardsToSecurity(array $guards): array
    {
        $security = [];
        foreach ($guards as $guard) {
            // normalize guard names (e.g. "role:admin" → "role")
            $key = explode(':', $guard)[0];

            switch ($key) {
                case 'auth':
                    $security[] = ['bearerAuth' => []];
                    break;
                case 'api':
                    $security[] = ['apiKeyAuth' => []];
                    break;
                case 'role':
                    $security[] = ['roleAuth' => []];
                    break;
                default:
                    $security[] = [$key . 'Auth' => []];
                    break;
            }
        }
        return $security ?: [['bearerAuth' => []]];
    }


    private static function cleanSummary(string $methodName): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $methodName));
    }
}
