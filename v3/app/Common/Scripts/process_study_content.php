<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Utilities\Logger;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\StudyContentGenerationService;

EnvLoader::load();
Logger::init();

$pdo     = DatabaseConnector::connect();
$service = new StudyContentGenerationService($pdo);

$limit = isset($argv[1]) && is_numeric($argv[1]) ? (int) $argv[1] : 4;

$service->process($limit);
