<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\AbandonedCart;
use V3\App\Services\Explore\BillingService;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendAbandonedCartEmail
{
    public function __invoke(AbandonedCart $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $billingService = new BillingService($pdo);
            $payment = $billingService->getAbandonedCartDetails($event->paymentId);

            if (
                empty($payment)
                || ($payment['status'] ?? '') !== 'abandoned'
                || !$billingService->shouldSendAbandonedCartReminder($event->paymentId)
            ) {
                return;
            }

            $email = trim((string) ($payment['email'] ?? ''));
            if ($email === '') {
                return;
            }

            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $subject = 'Complete your Linkskool subscription';
            $eventKey = sprintf('abandoned_cart:payment:%d', $event->paymentId);
            $fullName = trim((string) ($payment['first_name'] ?? '') . ' ' . (string) ($payment['last_name'] ?? ''));
            $recipientName = $fullName !== '' ? $fullName : 'Learner';

            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/abandoned_cart.php',
                [
                    'recipient_name' => $recipientName,
                    'plan_name' => (string) ($payment['plan_name'] ?? 'subscription'),
                    'amount' => (float) ($payment['amount'] ?? 0),
                    'reference' => (string) ($payment['reference'] ?? ''),
                    'expires_at' => (string) ($payment['expires_at'] ?? ''),
                    'platform' => (string) ($payment['platform'] ?? ''),
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
