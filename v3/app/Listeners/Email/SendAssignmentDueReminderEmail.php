<?php

namespace V3\App\Listeners\Email;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Lesson\AssignmentDueReminderDue;
use V3\App\Services\Explore\LessonNotificationTargetService;

class SendAssignmentDueReminderEmail
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

            $subject = 'Assignment Due Reminder: ' . ($lesson['title'] ?? 'Lesson');
            $lessonTitle = htmlspecialchars((string) ($lesson['title'] ?? 'Lesson'), ENT_QUOTES, 'UTF-8');
            $dueDate = htmlspecialchars((string) ($lesson['assignment_due_date'] ?? ''), ENT_QUOTES, 'UTF-8');

            $recipients = $service->getRecipientsForLesson($event->lessonId);
            foreach ($recipients as $recipient) {
                if (empty($recipient['email'])) {
                    continue;
                }

                if (!$service->shouldSendAssignmentReminder($event->lessonId, (int) $recipient['profile_id'])) {
                    continue;
                }

                $name = trim(
                    (string) ($recipient['first_name'] ?? '') . ' ' .
                    (string) ($recipient['last_name'] ?? '')
                );
                $name = $name !== '' ? $name : 'Student';

                $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                $html = "<p>Hello {$safeName},</p>
                    <p>This is a reminder that your lesson deliverables for <strong>{$lessonTitle}</strong> are due by <strong>{$dueDate}</strong>.</p>
                    <p>Please submit your quiz and assignment before the deadline.</p>";

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
