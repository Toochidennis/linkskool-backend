<?php

namespace V3\App\Common\Utilities;

class ResponseHandler
{
    public static function sendJsonResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
