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

        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbCharset} COLLATE {$dbCollation}",
        ];

        return new PDO($dsn, $dbUser, $dbPass, $options);
    }
}
