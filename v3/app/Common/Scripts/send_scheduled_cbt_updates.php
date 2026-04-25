<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Events\Registrars\RegisterEventListeners;
use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Utilities\Logger;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\CbtUpdateService;

EnvLoader::load();
Logger::init();
RegisterEventListeners::register();

$pdo = DatabaseConnector::connect();
$service = new CbtUpdateService($pdo);

$processed = $service->dispatchScheduledUpdates();

echo sprintf("Scheduled CBT updates dispatched: %d\n", $processed);
