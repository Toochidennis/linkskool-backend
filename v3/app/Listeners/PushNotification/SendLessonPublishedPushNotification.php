<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\LessonPublished;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendLessonPublishedPushNotification
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

            $lessonTitle = (string) ($lesson['title'] ?? 'your lesson');
            $title = 'New Lesson Published';
            $body = sprintf('%s is now available.', $lessonTitle);

            $data = [
                'type' => 'lesson_published',
                'lesson_id' => (string) $event->lessonId,
                'cohort_id' => (string) ($lesson['cohort_id'] ?? ''),
                'profile_id' => '',
                'course_id' => (string) ($lesson['course_id'] ?? ''),
                'program_id' => (string) ($lesson['program_id'] ?? ''),
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
