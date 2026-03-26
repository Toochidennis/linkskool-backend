<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\AbandonedCart;
use V3\App\Services\Explore\BillingService;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendAbandonedCartPushNotification
{
    public function __invoke(AbandonedCart $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $billingService = new BillingService($pdo);
            $payment = $billingService->getAbandonedCartDetails($event->paymentId);

            if (empty($payment) || ($payment['status'] ?? '') !== 'abandoned') {
                return;
            }

            $userId = (int) ($payment['user_id'] ?? 0);
            if ($userId <= 0) {
                return;
            }

            $planName = trim((string) ($payment['plan_name'] ?? 'your subscription'));
            $title = 'Complete Your Subscription';
            $body = sprintf('Your %s checkout is still waiting. Come back and finish your payment.', $planName);
            $data = [
                'type' => 'abandoned_cart',
                'payment_id' => (string) $event->paymentId,
                'plan_id' => (string) ($payment['plan_id'] ?? ''),
                'reference' => (string) ($payment['reference'] ?? ''),
                'platform' => (string) ($payment['platform'] ?? ''),
                'event_key' => sprintf('abandoned_cart:payment:%d', $event->paymentId),
            ];

            (new LessonNotificationTargetService($pdo))->sendPushOnce($userId, $title, $body, $data);
        } catch (\Throwable) {
            return;
        }
    }
}
