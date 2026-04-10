<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Utilities\EnvLoader;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\CourseCohortEnrollmentService;

EnvLoader::load();

$pdo = DatabaseConnector::connect();
$service = new CourseCohortEnrollmentService($pdo);

$abandonedCount = $service->abandonExpiredPendingPayments();

echo sprintf(
    "[%s] Marked %d expired pending course cohort payment(s) as abandoned.\n",
    date('Y-m-d H:i:s'),
    $abandonedCount
);
