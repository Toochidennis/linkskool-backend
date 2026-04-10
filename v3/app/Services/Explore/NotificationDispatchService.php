<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Email\AbandonedCart;
use V3\App\Events\Email\AbandonedCourseCohortCart;
use V3\App\Events\Lesson\AssignmentDueReminderDue;
use V3\App\Events\Lesson\ClassReminderDue;
use V3\App\Events\Lesson\LiveClassReminderDue;

class NotificationDispatchService
{
    public function dispatchReminder(array $data, array $resolved): array
    {
        $targets = $resolved['targets'] ?? [];
        $reminderType = (string) $data['reminder_type'];

        foreach ($targets as $target) {
            $id = (int) ($target['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            match ($reminderType) {
                'abandoned_cart' => EventDispatcher::dispatch(new AbandonedCart($id)),
                'abandoned_course_cohort_cart' => EventDispatcher::dispatch(
                    new AbandonedCourseCohortCart($id, (string) ($resolved['cadence'] ?? 'generic'))
                ),
                'class_reminder' => EventDispatcher::dispatch(new ClassReminderDue($id)),
                'live_class_reminder' => EventDispatcher::dispatch(new LiveClassReminderDue($id)),
                'assignment_due_reminder' => EventDispatcher::dispatch(new AssignmentDueReminderDue($id)),
                default => throw new \InvalidArgumentException('Unsupported reminder type.'),
            };
        }

        $response = [
            'mode' => 'reminder',
            'audience' => (string) $data['audience'],
            'reminder_type' => $reminderType,
            'processed_count' => \count($targets),
        ];

        if (isset($resolved['cadence'])) {
            $response['cadence'] = (string) $resolved['cadence'];
        }

        return $response;
    }
}
