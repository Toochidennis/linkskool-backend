<?php

namespace V3\App\Utilities;

class DataExtractor
{
    public static function extractPostData()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            // JSON Request
            $post = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                $response['message'] = 'Invalid JSON payload. Ensure that all fields are provided.';
                ResponseHandler::sendJsonResponse($response);
            }
            return $post;
        } elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
            // Form-urlencoded Request
            return $_POST;
        } else {
            http_response_code(400);
            $response['message'] = 'Unsupported Content-Type.';
            ResponseHandler::sendJsonResponse($response);
        }
    }
}
