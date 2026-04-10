<?php

namespace V3\App\Services\Explore;

class NotificationCampaignService
{
    private NotificationAudienceResolverService $audienceResolver;
    private NotificationDispatchService $dispatchService;

    public function __construct(private \PDO $pdo)
    {
        $this->audienceResolver = new NotificationAudienceResolverService($pdo);
        $this->dispatchService = new NotificationDispatchService();
    }

    public function getOptions(): array
    {
        return $this->audienceResolver->getOptions();
    }

    public function send(array $data): array
    {
        if (($data['mode'] ?? null) !== 'reminder') {
            throw new \InvalidArgumentException('Only reminder mode is currently supported.');
        }

        $this->guardReminderSelection($data);

        $resolved = $this->audienceResolver->resolveReminderTargets($data);
        return $this->dispatchService->dispatchReminder($data, $resolved);
    }

    private function guardReminderSelection(array $data): void
    {
        $audience = (string) ($data['audience'] ?? '');
        $reminderType = (string) ($data['reminder_type'] ?? '');

        $allowed = [
            'dropped_subscription_cart_users' => ['abandoned_cart'],
            'dropped_course_cohort_cart_users' => ['abandoned_course_cohort_cart'],
            'all_enrolled_users' => [
                'class_reminder',
                'live_class_reminder',
                'assignment_due_reminder',
            ],
            'program_enrolled_users' => [
                'class_reminder',
                'live_class_reminder',
                'assignment_due_reminder',
            ],
            'cohort_enrolled_users' => [
                'class_reminder',
                'live_class_reminder',
                'assignment_due_reminder',
            ],
        ];

        if (!isset($allowed[$audience])) {
            throw new \InvalidArgumentException('Unsupported audience.');
        }

        if (!\in_array($reminderType, $allowed[$audience], true)) {
            throw new \InvalidArgumentException('Selected audience does not support this reminder type.');
        }

        if ($audience === 'program_enrolled_users' && (int) ($data['program_id'] ?? 0) <= 0) {
            throw new \InvalidArgumentException('program_id is required for the selected audience.');
        }

        if ($audience === 'cohort_enrolled_users' && (int) ($data['cohort_id'] ?? 0) <= 0) {
            throw new \InvalidArgumentException('cohort_id is required for the selected audience.');
        }

        if (
            $reminderType === 'abandoned_course_cohort_cart'
            && isset($data['cadence'])
            && !\in_array((string) $data['cadence'], ['generic', 'one_hour'], true)
        ) {
            throw new \InvalidArgumentException('Unsupported cadence.');
        }
    }
}
