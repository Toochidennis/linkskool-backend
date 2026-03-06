<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\LiveClassReminderDue;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendLiveClassReminderPushNotification
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

            $title = 'Live Class Reminder';
            $body = sprintf('Your live class %s starts soon.', (string) ($lesson['title'] ?? 'Lesson'));

            $data = [
                'type' => 'live_class_reminder',
                'lesson_id' => (string) $event->lessonId,
                'cohort_id' => (string) ($lesson['cohort_id'] ?? ''),
                'profile_id' => '',
                'course_id' => (string) ($lesson['course_id'] ?? ''),
                'program_id' => (string) ($lesson['program_id'] ?? ''),
                'event_key' => sprintf('live_class_reminder:lesson:%d', $event->lessonId),
            ];

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            $service->sendPushInBatches($recipients, $title, $body, $data);
        } catch (\Throwable) {
            return;
        }
    }
}
