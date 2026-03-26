<?php

require_once __DIR__ . '/../vendor/autoload.php';

V3\App\Common\Utilities\EnvLoader::load();

$templatePath = __DIR__ . '/../app/Templates/emails/payment_receipt.php';

$data = [
    'recipient_name' => 'Dennis Toochi',
    'status' => 'success',
    'plan_name' => 'Premium CBT Plan',
    'amount' => 150000,
    'reference' => 'CBT-20260326-ABC123',
    'platform' => 'mobile',
    'method' => 'online',
    'payment_message' => 'Payment verified successfully.',
    'paid_at' => date('Y-m-d H:i:s'),
];

echo V3\App\Common\Utilities\TemplateRenderer::render($templatePath, $data);
