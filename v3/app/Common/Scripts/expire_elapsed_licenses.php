<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use V3\App\Common\Utilities\EnvLoader;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\LicenseService;

EnvLoader::load();

$pdo = DatabaseConnector::connect();
$licenseService = new LicenseService($pdo);

$expiredCount = $licenseService->expireElapsedLicenses();

echo sprintf(
    "[%s] Marked %d elapsed license(s) as expired.\n",
    date('Y-m-d H:i:s'),
    $expiredCount
);
