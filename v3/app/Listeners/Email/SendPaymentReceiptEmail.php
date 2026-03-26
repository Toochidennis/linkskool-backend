<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\PaymentReceipt;
use V3\App\Services\Explore\BillingService;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendPaymentReceiptEmail
{
    public function __invoke(PaymentReceipt $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $billingService = new BillingService($pdo);
            $payment = $billingService->getPaymentReceiptDetails($event->paymentId);

            if (empty($payment)) {
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

            $status = (string) ($payment['status'] ?? 'pending');
            if (!in_array($status, ['success', 'failed'], true)) {
                return;
            }

            $recipientName = trim((string) ($payment['first_name'] ?? '') . ' ' . (string) ($payment['last_name'] ?? ''));
            $recipientName = $recipientName !== '' ? $recipientName : 'Learner';
            $planName = (string) ($payment['plan_name'] ?? 'CBT plan');
            $subject = $status === 'success'
                ? 'Payment Receipt: ' . $planName
                : 'Payment Update: ' . $planName;
            $eventKey = sprintf('payment_receipt:payment:%d:status:%s', $event->paymentId, $status);

            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/payment_receipt.php',
                [
                    'recipient_name' => $recipientName,
                    'status' => $status,
                    'plan_name' => $planName,
                    'amount' => (float) ($payment['amount'] ?? 0),
                    'reference' => (string) ($payment['reference'] ?? ''),
                    'platform' => (string) ($payment['platform'] ?? ''),
                    'method' => (string) ($payment['method'] ?? ''),
                    'payment_message' => (string) ($payment['message'] ?? ''),
                    'paid_at' => (string) ($payment['paid_at'] ?? ''),
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
