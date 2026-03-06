<?php

namespace V3\App\Listeners\Email;

use V3\App\Common\Utilities\TemplateRenderer;
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

            $v3root = realpath(__DIR__ . '/../../');
            if ($v3root === false) {
                return;
            }

            $eventKey = sprintf('lesson_published:lesson:%d', $event->lessonId);
            $subject = 'New Lesson Published: ' . htmlspecialchars((string) ($lesson['title']), ENT_QUOTES, 'UTF-8');
            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/lesson_published.php',
                array_merge($lesson, [
                    'first_name' => '',
                    'last_name' => '',
                ])
            );

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            $service->sendEmailInBatches($recipients, $subject, $html, $eventKey);
        } catch (\Throwable) {
            return;
        }
    }
}
