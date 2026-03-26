<?php

require_once __DIR__ . '/../vendor/autoload.php';

V3\App\Common\Utilities\EnvLoader::load();

$templatePath = __DIR__ . '/../app/Templates/emails/license_activated.php';

$data = [
    'recipient_name' => 'Dennis Toochi',
    'plan_name' => 'Premium CBT Plan',
    'platform' => 'mobile',
    'reference' => 'CBT-20260326-ABC123',
    'issued_at' => date('Y-m-d H:i:s'),
    'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
    'duration_days' => 30,
];

echo V3\App\Common\Utilities\TemplateRenderer::render($templatePath, $data);
