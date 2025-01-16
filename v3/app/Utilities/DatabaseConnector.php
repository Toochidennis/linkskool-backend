<?php

namespace V3\App\Utilities;

use PDO;

class DatabaseConnector
{
    private function __construct() {}

    public static function connect($dbname = '')
    {
        // Load environment variables if required
        EnvLoader::load();

        # Database configurations
        $dbHost = getenv('DB_HOST');
        $dbName = empty($dbname) ? getenv('DB_NAME') : $dbname;
        $dbUser = getenv('DB_USER');
        $dbPass = getenv('DB_PASS');

        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (\PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());

            http_response_code(500);
            ResponseHandler::sendJsonResponse(['success' => false, 'message' => 'Unable to connect to the database.']);
        }
    }
}
