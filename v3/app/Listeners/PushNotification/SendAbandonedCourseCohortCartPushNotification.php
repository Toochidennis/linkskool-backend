<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\AbandonedCourseCohortCart;
use V3\App\Services\Explore\CourseCohortEnrollmentService;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendAbandonedCourseCohortCartPushNotification
{
    public function __invoke(AbandonedCourseCohortCart $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new CourseCohortEnrollmentService($pdo);
            $payment = $service->getAbandonedCartDetails($event->paymentId);

            if (
                empty($payment)
                || ($payment['status'] ?? '') !== 'abandoned'
                || !$service->shouldSendAbandonedCartReminder($event->paymentId)
            ) {
                return;
            }

            $profileId = (int) ($payment['profile_id'] ?? 0);
            if ($profileId <= 0) {
                return;
            }

            $profileRows = (new \V3\App\Models\Explore\ProgramProfile($pdo))
                ->select(['user_id'])
                ->where('id', $profileId)
                ->get();

            $userId = (int) ($profileRows[0]['user_id'] ?? 0);
            if ($userId <= 0) {
                return;
            }

            $items = trim((string) ($payment['checkout_items'] ?? 'your selected courses'));
            $title = 'Complete Your Course Checkout';
            $body = sprintf('Your checkout for %s is still waiting. Come back and finish payment.', $items);
            $data = [
                'type' => 'abandoned_course_cohort_cart',
                'payment_id' => (string) $event->paymentId,
                'reference' => (string) ($payment['reference'] ?? ''),
                'platform' => (string) ($payment['platform'] ?? ''),
                'event_key' => sprintf(
                    'abandoned_course_cohort_cart:%s:payment:%d',
                    $event->cadence,
                    $event->paymentId
                ),
            ];

            (new LessonNotificationTargetService($pdo))->sendPushOnce($userId, $title, $body, $data);
        } catch (\Throwable) {
            return;
        }
    }
}
