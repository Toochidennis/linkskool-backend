<?php
use V3\App\Utilities\EnvLoader;


EnvLoader::load(__DIR__ . '/.env');

# Database configurations
$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');




