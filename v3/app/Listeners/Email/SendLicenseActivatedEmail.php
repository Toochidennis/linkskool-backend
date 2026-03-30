<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\LicenseActivated;
use V3\App\Services\Explore\LessonNotificationTargetService;
use V3\App\Services\Explore\LicenseService;

class SendLicenseActivatedEmail
{
    public function __invoke(LicenseActivated $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $licenseService = new LicenseService($pdo);
            $license = $licenseService->getActivationReceiptDetails($event->licenseId);

            if (empty($license) || ($license['status'] ?? '') !== 'active') {
                return;
            }

            $email = trim((string) ($license['email'] ?? ''));
            if ($email === '') {
                return;
            }

            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $recipientName = trim((string) ($license['first_name'] ?? '') . ' ' . (string) ($license['last_name'] ?? ''));
            $recipientName = $recipientName !== '' ? $recipientName : 'Learner';
            $planName = (string) ($license['plan_name'] ?? 'CBT plan');
            $subject = 'Activation Receipt: ' . $planName;
            $eventKey = sprintf('license_activated:license:%d', $event->licenseId);

            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/license_activated.php',
                [
                    'recipient_name' => $recipientName,
                    'plan_name' => $planName,
                    'platform' => (string) ($license['platform'] ?? ''),
                    'reference' => (string) ($license['reference'] ?? ''),
                    'issued_at' => (string) ($license['issued_at'] ?? ''),
                    'expires_at' => (string) ($license['expires_at'] ?? ''),
                    'duration_days' => $license['duration_days'] ?? null,
                ]
            );

            (new LessonNotificationTargetService($pdo))->sendEmailOnce(
                $email,
                $recipientName,
                $subject,
                $html,
                $eventKey
            );
        } catch (\Throwable) {
            return;
        }
    }
}
