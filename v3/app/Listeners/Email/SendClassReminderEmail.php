<?php

namespace V3\App\Listeners\Email;

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

            $subject = 'Class Reminder: ' . ($lesson['title'] ?? 'Upcoming Lesson');
            $joinUrl = '';
            $zoomInfo = json_decode((string) ($lesson['zoom_info'] ?? ''), true);
            if (\is_array($zoomInfo)) {
                $joinUrl = (string) ($zoomInfo['url'] ?? '');
            }

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

                $html = $service->renderClassReminderEmail([
                    'student_name' => $name,
                    'lesson_title' => (string) ($lesson['title'] ?? 'Upcoming Lesson'),
                    'class_date' => (string) ($lesson['lesson_date'] ?? ''),
                    'class_time' => '',
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
