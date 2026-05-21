<?php

namespace V3\App\Common\Utilities;

class Sanitizer
{
    // Use this for general text input sanitization
    public static function sanitizeInput($input, string $parentKey = ''): array|string|null
    {
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);

        $process = function ($value, $keyPath) use (&$process, $purifier): array|string|null {
            if ($value === null) {
                return null;
            }

            if (\is_array($value)) {
                $sanitized = [];
                foreach ($value as $k => $v) {
                    $sanitized[$k] = $process($v, $keyPath . ($keyPath ? '.' : '') . $k);
                }
                return $sanitized;
            }

            return $purifier->purify(trim((string)$value));
        };

        return $process($input, $parentKey);
    }
}
