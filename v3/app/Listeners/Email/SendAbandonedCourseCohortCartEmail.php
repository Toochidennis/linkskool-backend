<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\AbandonedCourseCohortCart;
use V3\App\Services\Explore\CourseCohortEnrollmentService;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendAbandonedCourseCohortCartEmail
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

            $email = trim((string) ($payment['email'] ?? ''));
            if ($email === '') {
                return;
            }

            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $subject = 'Complete your course checkout';
            $eventKey = sprintf(
                'abandoned_course_cohort_cart:%s:payment:%d',
                $event->cadence,
                $event->paymentId
            );
            $fullName = trim((string) ($payment['first_name'] ?? '') . ' ' . (string) ($payment['last_name'] ?? ''));
            $recipientName = $fullName !== '' ? $fullName : 'Learner';

            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/abandoned_course_cohort_cart.php',
                [
                    'recipient_name' => $recipientName,
                    'checkout_items' => (string) ($payment['checkout_items'] ?? ''),
                    'amount' => (float) ($payment['amount'] ?? 0),
                    'reference' => (string) ($payment['reference'] ?? ''),
                    'expires_at' => (string) ($payment['expires_at'] ?? ''),
                    'platform' => (string) ($payment['platform'] ?? ''),
                    'course_id' => (int) ($payment['primary_course_id'] ?? 0),
                    'cohort_slug' => (string) ($payment['primary_cohort_slug'] ?? ''),
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
