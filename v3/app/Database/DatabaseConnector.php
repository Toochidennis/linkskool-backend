<?php

namespace V3\App\Database;

use PDO;
use PDOException;
use V3\App\Common\Utilities\ResponseHandler;

class DatabaseConnector
{
    public static function connect($dbname = '')
    {
        // Database configurations
        $dbHost = getenv('DB_HOST');
        $dbName = empty($dbname) ? getenv('DB_NAME') : $dbname;
        $dbUser = getenv('DB_USER');
        $dbPass = getenv('DB_PASS');

        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            ResponseHandler::sendJsonResponse(
                [
                    'statusCode' => 500,
                    'success' => false,
                    'message' => 'Unable to connect to the database.'
                ]
            );
        }
    }
}
