<?php
namespace V3\App\Utilities;

class ResponseHandler{
    public static function sendJsonResponse($response, $responseCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($responseCode);
        echo json_encode($response);
        exit;
    }
}