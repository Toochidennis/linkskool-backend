<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Utilities\Logger;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\TopicService;

EnvLoader::load();
Logger::init();

$pdo = DatabaseConnector::connect();
$service = new TopicService($pdo);

$service->processQuestions(5);
