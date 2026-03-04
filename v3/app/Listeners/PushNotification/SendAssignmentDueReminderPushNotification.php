<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\AssignmentDueReminderDue;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendAssignmentDueReminderPushNotification
{
    public function __invoke(AssignmentDueReminderDue $event): void
    {
        try {
            $pdo = DatabaseConnector::connect();
            $service = new LessonNotificationTargetService($pdo);
            $lesson = $service->getLesson($event->lessonId);

            if (empty($lesson)) {
                return;
            }

            $title = 'Assignment Due Reminder';
            $body = sprintf(
                'Submit pending quiz/assignment for %s before the deadline.',
                (string) ($lesson['title'])
            );

            $data = [
                'type' => 'assignment_due_reminder',
                'lesson_id' => (string) $event->lessonId,
                'cohort_id' => (string) ($lesson['cohort_id'] ?? ''),
                'profile_id' => '',
                'course_id' => (string) ($lesson['course_id'] ?? ''),
                'program_id' => (string) ($lesson['program_id'] ?? ''),
                'event_key' => sprintf('assignment_due_reminder:lesson:%d', $event->lessonId),
            ];

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            foreach ($recipients as $recipient) {
                if (!$service->shouldSendAssignmentReminder($event->lessonId, (int) $recipient['profile_id'])) {
                    continue;
                }

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
