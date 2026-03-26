<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Utilities\EnvLoader;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\BillingService;

EnvLoader::load();

$pdo = DatabaseConnector::connect();
$billingService = new BillingService($pdo);

$abandonedCount = $billingService->abandonExpiredPendingPayments();

echo sprintf(
    "[%s] Marked %d expired pending payment(s) as abandoned.\n",
    date('Y-m-d H:i:s'),
    $abandonedCount
);
