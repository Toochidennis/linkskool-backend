<?php

namespace V3\App\Listeners\PushNotification;

use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\SubmissionGraded;
use V3\App\Models\Common\UserDeviceToken;
use V3\App\Models\Explore\ProgramCourseCohortLesson;
use V3\App\Models\Explore\ProgramProfile;
use V3\App\Services\Common\NotificationService;

class SendSubmissionGradedPushNotification
{
    public function __invoke(SubmissionGraded $event): void
    {
        $pdo = DatabaseConnector::connect();

        $profile = (new ProgramProfile($pdo))
            ->where('id', $event->profileId)
            ->first();

        if (empty($profile)) {
            return;
        }

        $lesson = (new ProgramCourseCohortLesson($pdo))
            ->where('id', $event->lessonId)
            ->first();

        $tokens = $this->resolveDeviceTokens(
            new UserDeviceToken($pdo),
            (int) ($profile['user_id'] ?? 0),
            (int) $event->profileId
        );

        if (empty($tokens)) {
            return;
        }

        $lessonTitle = (string) ($lesson['title'] ?? 'your lesson');
        $title = 'Assignment Graded';
        $body = sprintf(
            'Your submission for %s has been graded. Score: %s%%',
            $lessonTitle,
            (string) $event->assignedScore
        );

        $notificationService = new NotificationService($pdo);
        foreach ($tokens as $token) {
            $notificationService->send($token, $title, $body);
        }
    }

    private function resolveDeviceTokens(
        UserDeviceToken $userDeviceToken,
        int $userId,
        int $profileId
    ): array {
        if ($userId <= 0 && $profileId <= 0) {
            return [];
        }

        $columns = $userDeviceToken->rawQuery('SHOW COLUMNS FROM user_device_tokens');
        $fields = array_map(
            static fn(array $row): string => (string) ($row['Field'] ?? ''),
            $columns
        );

        $tokenColumn = $this->firstExistingField(
            $fields,
            ['device_token', 'token', 'fcm_token', 'push_token']
        );

        if ($tokenColumn === null) {
            return [];
        }

        $params = [];
        $filters = [];

        if ($userId > 0 && in_array('user_id', $fields, true)) {
            $filters[] = 'user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($profileId > 0 && in_array('profile_id', $fields, true)) {
            $filters[] = 'profile_id = :profile_id';
            $params['profile_id'] = $profileId;
        }

        if (empty($filters)) {
            return [];
        }

        $sql = sprintf(
            'SELECT %s AS token FROM user_device_tokens WHERE (%s)',
            $tokenColumn,
            implode(' OR ', $filters)
        );

        $rows = $userDeviceToken->rawQuery($sql, $params);
        $tokens = array_map(
            static fn(array $row): string => trim((string) ($row['token'] ?? '')),
            $rows
        );

        return array_values(array_unique(array_filter($tokens, static fn(string $v): bool => $v !== '')));
    }

    private function firstExistingField(array $existing, array $preferred): ?string
    {
        foreach ($preferred as $field) {
            if (in_array($field, $existing, true)) {
                return $field;
            }
        }

        return null;
    }
}
