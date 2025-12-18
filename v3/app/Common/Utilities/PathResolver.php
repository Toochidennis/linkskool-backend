<?php

namespace V3\App\Common\Utilities;

class PathResolver
{
    public static function getContentPaths(): array
    {
        $v3root = realpath(__DIR__ . '/../../../../../');

        if (getenv('APP_ENV') === 'development') {
            $v3root = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR . 'public';
        }

        if (!$v3root) {
            throw new \RuntimeException("Could not resolve v3 root path.");
        }

        $db = $_SESSION['_db'] ?? 'lrm_default_lrm';
        $dbName = explode('_', $db)[2] ?? 'default_lrm';
        $relativePath = "assets/elearning/$dbName/";
        $contentPath = $v3root . DIRECTORY_SEPARATOR . $relativePath;

        if (!is_dir($contentPath) && !mkdir($contentPath, 0755, true)) {
            throw new \RuntimeException("Failed to create directory: {$contentPath}");
        }

        return [
            'relative' => $relativePath,
            'absolute' => $contentPath,
        ];
    }
}
