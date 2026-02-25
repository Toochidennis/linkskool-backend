<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Auth\UserRegisteredFirstTime;
use V3\App\Services\Common\MailService;

class SendWelcomeEmail
{
    public function __invoke(UserRegisteredFirstTime $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $mailService = new MailService($pdo);
            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $name = trim($event->firstName . ' ' . $event->lastName);

            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/registration.php',
                [
                    'student_name' => $name,
                ]
            );

            $mailService->send(
                "{$name} <{$event->email}>",
                'Welcome to Linkskool',
                $html
            );
        } catch (\Throwable) {
            return;
        }
    }
}
