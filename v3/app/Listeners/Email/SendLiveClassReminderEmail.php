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
            $recipients = $service->getRecipientsForLesson($event->lessonId);
            foreach ($recipients as $recipient) {
                if (empty($recipient['email'])) {
                    continue;
                }

                $recipientName = trim(
                    (string) ($recipient['first_name'] ?? '') . ' ' .
                        (string) ($recipient['last_name'] ?? '')
                ) ?: 'Learner';

                $subject = 'Live Class Starting Soon: ' . htmlspecialchars((string) ($lesson['title'] ?? 'Live Class'), ENT_QUOTES, 'UTF-8');

                // Prepare lesson data for template
                $templateData = array_merge($lesson, [
                    'first_name' => $recipient['first_name'] ?? '',
                    'last_name' => $recipient['last_name'] ?? '',
                    'zoom_info' => $zoomInfo,
                ]);

                $html = TemplateRenderer::render(
                    $v3root . '/Templates/emails/lesson_class_reminder.php',
                    $templateData
                );

                $service->sendEmailOnce(
                    (string) $recipient['email'],
                    $recipientName,
                    $subject,
                    $html,
                    $eventKey
                );
            }
        } catch (\Throwable) {
            return;
        }
    }
}
