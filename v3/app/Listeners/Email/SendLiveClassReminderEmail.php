<?php

namespace V3\App\Listeners\Email;

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

            $zoomInfo = json_decode((string) ($lesson['zoom_info'] ?? ''), true);
            if (!\is_array($zoomInfo)) {
                return;
            }

            $subject = 'Live Class Starts Soon: ' . htmlspecialchars((string) ($lesson['title'] ?? 'Live Class'), ENT_QUOTES, 'UTF-8');
            $joinUrl = (string) ($zoomInfo['url'] ?? '');
            $startTime = (string) ($zoomInfo['start_time'] ?? '');

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            foreach ($recipients as $recipient) {
                if (empty($recipient['email'])) {
                    continue;
                }

                $name = trim(
                    (string) ($recipient['first_name'] ?? '') . ' ' .
                    (string) ($recipient['last_name'] ?? '')
                );

                $html = $service->renderClassReminderEmail([
                    'student_name' => $name,
                    'lesson_title' => (string) $lesson['title'],
                    'class_date' => (string) ($lesson['lesson_date'] ?? ''),
                    'class_time' => $startTime,
                    'join_url' => $joinUrl,
                ]);

                if ($html === '') {
                    continue;
                }

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
