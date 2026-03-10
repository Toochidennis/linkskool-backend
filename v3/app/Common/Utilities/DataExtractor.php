<?php

namespace V3\App\Common\Utilities;

class DataExtractor
{
    private static ?string $rawBody = null;

    public static function extractPostData()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            // JSON Request
            if (self::$rawBody === null) {
                self::$rawBody = file_get_contents('php://input') ?: '';
            }

            $post = json_decode(self::$rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                $response['message'] = 'Invalid JSON payload. Ensure that all fields are provided.';
                ResponseHandler::sendJsonResponse($response);
            }
            return $post;
        } elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
            // Form-urlencoded Request
            return $_POST;
        } elseif (stripos($contentType, 'multipart/form-data') !== false) {
            // Multipart Form Data
            $data = $_POST;
            $files = $_FILES;
            return [...$data, ...$files];
        } else {
            http_response_code(400);
            $response['message'] = 'Unsupported Content-Type.';
            ResponseHandler::sendJsonResponse($response);
        }
    }

    public static function getRawBody(): string
    {
        if (self::$rawBody === null) {
            self::$rawBody = file_get_contents('php://input') ?: '';
        }

        return self::$rawBody;
    }
}
