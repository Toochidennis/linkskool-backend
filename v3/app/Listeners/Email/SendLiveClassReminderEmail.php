<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\LiveClassReminderDue;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendLiveClassReminderEmail
{
    public function __invoke(LiveClassReminderDue $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new LessonNotificationTargetService($pdo);

            $lesson = $service->getLesson($event->lessonId);
            if (empty($lesson)) {
                return;
            }

            $zoomInfo = json_decode((string) ($lesson['zoom_info'] ?? ''), true) ?: [];
            if (empty($zoomInfo) && empty($lesson['video_url'])) {
                return;
            }

            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $eventKey = sprintf('live_class_reminder:lesson:%d', $event->lessonId);
            $subject = 'Live Class Starting Soon: ' . htmlspecialchars((string) ($lesson['title'] ?? 'Live Class'), ENT_QUOTES, 'UTF-8');
            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/lesson_class_reminder.php',
                array_merge($lesson, [
                    'first_name' => '',
                    'last_name' => '',
                    'zoom_info' => $zoomInfo,
                ])
            );

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            $service->sendEmailInBatches($recipients, $subject, $html, $eventKey);
        } catch (\Throwable) {
            return;
        }
    }
}
