<?php

namespace V3\App\Common\Utilities;

class AssetUrl
{
    public static function fromAppUrl(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $path) === 1 || str_starts_with($path, 'data:')) {
            return str_replace(' ', '%20', $path);
        }

        $appUrl = rtrim((string) getenv('APP_URL'), '/');
        if ($appUrl === '') {
            return str_replace(' ', '%20', $path);
        }

        return $appUrl . '/' . str_replace(' ', '%20', ltrim($path, '/'));
    }
}
