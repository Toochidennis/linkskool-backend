<?php

namespace V3\App\Listeners\Email;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\LessonPublished;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendLessonPublishedEmail
{
    public function __invoke(LessonPublished $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new LessonNotificationTargetService($pdo);

            $lesson = $service->getLesson($event->lessonId);
            if (empty($lesson)) {
                return;
            }

            $subject = 'New Lesson Published: ' . ($lesson['title'] ?? 'Lesson');
            $lessonTitle = htmlspecialchars((string) ($lesson['title'] ?? 'Lesson'), ENT_QUOTES, 'UTF-8');
            $html = "<p>A new lesson has been published: <strong>{$lessonTitle}</strong>.</p>";

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            foreach ($recipients as $recipient) {
                if (empty($recipient['email'])) {
                    continue;
                }

                $name = trim(
                    (string) ($recipient['first_name'] ?? '') . ' ' .
                    (string) ($recipient['last_name'] ?? '')
                );
                $name = $name !== '' ? $name : 'Student';

                $service->sendEmailOnce(
                    (string) $recipient['email'],
                    $name,
                    $subject,
                    $html
                );
            }
        } catch (\Throwable) {
            return;
        }
    }
}
