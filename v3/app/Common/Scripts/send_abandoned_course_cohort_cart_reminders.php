<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Events\EventDispatcher;
use V3\App\Common\Events\Registrars\RegisterEventListeners;
use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Utilities\Logger;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\AbandonedCourseCohortCart;
use V3\App\Services\Explore\CourseCohortEnrollmentService;

EnvLoader::load();
Logger::init();
RegisterEventListeners::register();

$pdo = DatabaseConnector::connect();
$service = new CourseCohortEnrollmentService($pdo);
$rows = $service->getAbandonedCartReminderCandidates();

foreach ($rows as $row) {
    EventDispatcher::dispatch(new AbandonedCourseCohortCart((int) $row['id'], 'generic'));
}

echo sprintf("Dispatched abandoned course cohort cart reminders: %d\n", count($rows));
