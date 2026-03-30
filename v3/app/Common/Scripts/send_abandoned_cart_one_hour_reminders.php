<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Events\EventDispatcher;
use V3\App\Common\Events\Registrars\RegisterEventListeners;
use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Utilities\Logger;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\AbandonedCart;
use V3\App\Services\Explore\BillingService;

EnvLoader::load();
Logger::init();
RegisterEventListeners::register();

$pdo = DatabaseConnector::connect();
$billingService = new BillingService($pdo);
$rows = $billingService->getAbandonedCartReminderCandidatesAfterMinutes(60);

foreach ($rows as $row) {
    EventDispatcher::dispatch(new AbandonedCart((int) $row['id']));
}

echo sprintf("Dispatched one-hour abandoned cart reminders: %d\n", count($rows));
