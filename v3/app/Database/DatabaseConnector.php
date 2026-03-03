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
        $dbCollation = getenv('COLLATION');
        $dbCharset = getenv('CHARSET');
        $dbTimezone = getenv('DB_TIMEZONE') ?: 'Africa/Lagos';
        $dbTimezoneOffset = '+01:00';

        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbCharset} COLLATE {$dbCollation}",
        ];

        $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

        try {
            $pdo->exec("SET time_zone = '{$dbTimezone}'");
        } catch (\PDOException $exception) {
            $pdo->exec("SET time_zone = '{$dbTimezoneOffset}'");
        }

        return $pdo;
    }
}
