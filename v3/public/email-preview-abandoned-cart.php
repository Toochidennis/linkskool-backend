<?php

require_once __DIR__ . '/../vendor/autoload.php';

V3\App\Common\Utilities\EnvLoader::load();

$templatePath = __DIR__ . '/../app/Templates/emails/abandoned_cart.php';

$data = [
    'recipient_name' => 'Dennis Toochi',
    'plan_name' => 'Premium CBT Plan',
    'reference' => 'LSK-2026-000142',
    'platform' => 'mobile',
    'expires_at' => date('F d, Y g:i A', strtotime('+10 minutes')),
    'amount' => 150000,
];

echo V3\App\Common\Utilities\TemplateRenderer::render($templatePath, $data);
