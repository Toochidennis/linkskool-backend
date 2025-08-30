<?php

namespace V3\App\Common\Utilities;

class PathResolver
{
    public static function getContentPaths(): array
    {
        $v3root = realpath(__DIR__ . '/../../../');

        if (!$v3root) {
            throw new \RuntimeException("Could not resolve v3 root path.");
        }

        $publicPath = "$v3root/public";

        $db = $_SESSION['_db'] ?? '';
        $parts = explode('_', $db);
        $dbName = $parts[2] ?? 'default_db';

        $relativePath = "assets/elearning/$dbName/";
        $contentPath = $publicPath . DIRECTORY_SEPARATOR . $relativePath;

        if (!is_dir($contentPath) && !mkdir($contentPath, 0755, true)) {
            throw new \RuntimeException("Failed to create directory: {$contentPath}");
        }

        return [
            'relative' => $relativePath,
            'absolute' => $contentPath,
        ];
    }
}