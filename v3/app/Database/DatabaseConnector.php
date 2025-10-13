<?php

namespace V3\App\Database;

use PDO;

class DatabaseConnector
{
    public static function connect($dbname = '')
    {
        // Database configurations
        $dbHost = getenv('DB_HOST');
        $dbName = empty($dbname) ? getenv('DB_NAME') : $dbname;
        $dbUser = getenv('DB_USER');
        $dbPass = getenv('DB_PASS');

        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
