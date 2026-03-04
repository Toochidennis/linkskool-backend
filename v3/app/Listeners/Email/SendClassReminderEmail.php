<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\ClassReminderDue;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendClassReminderEmail
{
    public function __invoke(ClassReminderDue $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new LessonNotificationTargetService($pdo);

            $lesson = $service->getLesson($event->lessonId);
            if (empty($lesson)) {
                return;
            }

            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $subject = 'Class Reminder: ' . htmlspecialchars((string) ($lesson['title'] ?? 'Class'), ENT_QUOTES, 'UTF-8');
            $eventKey = sprintf('class_reminder:lesson:%d', $event->lessonId);

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            foreach ($recipients as $recipient) {
                if (empty($recipient['email'])) {
                    continue;
                }

                $recipientName = trim(
                    (string) ($recipient['first_name'] ?? '') . ' ' .
                        (string) ($recipient['last_name'] ?? '')
                ) ?: 'Learner';

                // Prepare lesson data for template
                $templateData = array_merge($lesson, [
                    'first_name' => $recipient['first_name'] ?? '',
                    'last_name' => $recipient['last_name'] ?? '',
                ]);

                $html = TemplateRenderer::render(
                    $v3root . '/Templates/emails/class_reminder.php',
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
