<?php

namespace V3\App\Common\Utilities;

use V3\App\Common\Utilities\ResponseHandler;

class Sanitizer
{
    // Use this for general text input sanitization
    public static function sanitizeInput($input, string $parentKey = ''): array|string|null
    {
        $nullFields = [];

        // Recursive function to check for nulls and sanitize
        $process = function ($value, $keyPath) use (&$process, &$nullFields): array|string|null {
            if ($value === null) {
                $nullFields[] = $keyPath;
                return null; // Keep null as-is, but record it
            }

            if (is_array($value)) {
                $sanitized = [];
                foreach ($value as $k => $v) {
                    $sanitized[$k] = $process($v, $keyPath . ($keyPath ? '.' : '') . $k);
                }
                return $sanitized;
            }

            // Sanitize scalar value
            return htmlspecialchars(strip_tags(trim((string) $value)), ENT_QUOTES, 'UTF-8');
        };

        $sanitizedData = $process($input, $parentKey);

        // If any null fields found, return error
        if (!empty($nullFields)) {
            ResponseHandler::sendJsonResponse([
                'status' => false,
                'message' => 'Sanitization failed.',
                'error' => "The following fields cannot be null: " . implode(', ', $nullFields)
            ]);
            http_response_code(400);
        }

        return $sanitizedData;
    }
}
