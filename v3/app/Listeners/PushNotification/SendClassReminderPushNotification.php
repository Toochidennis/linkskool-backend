<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\ClassReminderDue;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendClassReminderPushNotification
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

            $title = 'Class Reminder';
            $body = sprintf('You have an upcoming class: %s.', (string) ($lesson['title'] ?? 'Lesson'));

            $data = [
                'type' => 'class_reminder',
                'lesson_id' => (string) $event->lessonId,
                'cohort_id' => (string) ($lesson['cohort_id'] ?? ''),
                'profile_id' => '',
                'course_id' => (string) ($lesson['course_id'] ?? ''),
                'program_id' => (string) ($lesson['program_id'] ?? ''),
                'event_key' => sprintf('class_reminder:lesson:%d', $event->lessonId),
            ];

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            foreach ($recipients as $recipient) {
                $service->sendPushOnce(
                    (int) $recipient['user_id'],
                    $title,
                    $body,
                    $data
                );
            }
        } catch (\Throwable) {
            return;
        }
    }
}
