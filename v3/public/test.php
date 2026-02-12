<?php

// Include the Autoloader (see "Libraries" for install instructions)
require_once __DIR__ . '/../vendor/autoload.php';
V3\App\Common\Utilities\EnvLoader::load();


// Use the Mailgun class from mailgun/mailgun-php v4.2
use Mailgun\Mailgun;

// Instantiate the client.
$mg = Mailgun::create(getenv('MAILGUN_SECRET_KEY') ?: 'API_KEY');
// When you have an EU-domain, you must specify the endpoint:
// $mg = Mailgun::create(getenv('API_KEY') ?: 'API_KEY', 'https://api.eu.mailgun.net');

// Compose and send your message.
$result = $mg->messages()->send(
    getenv('MAILGUN_DOMAIN') ?: 'DOMAIN_NAME',
    [
        'from' => 'Mailgun Sandbox <no-reply@mail.linkskool.net>',
        'to' => 'Toochi Dennis <dennistoochi@gmail.com>',
        'subject' => 'Hello Toochi Dennis',
        'text' => 'Congratulations Toochi Dennis, you just sent an email with Mailgun! You are truly awesome!'
    ]
);

print_r($result->getMessage());
