<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Common\Notification;
use V3\App\Models\Common\UserDeviceToken;
use V3\App\Models\Explore\Discussion;
use V3\App\Models\Explore\DiscussionPost;
use V3\App\Services\Common\NotificationService;

class DiscussionNotificationTargetService
{
    private Discussion $discussionModel;
    private DiscussionPost $discussionPostModel;
    private Notification $notificationLog;
    private UserDeviceToken $userDeviceToken;
    private NotificationService $notificationService;
    private ?bool $notificationLogSupportsEventKey = null;

    public function __construct(\PDO $pdo)
    {
        $this->discussionModel = new Discussion($pdo);
        $this->discussionPostModel = new DiscussionPost($pdo);
        $this->notificationLog = new Notification($pdo);
        $this->userDeviceToken = new UserDeviceToken($pdo);
        $this->notificationService = new NotificationService($pdo);
    }

    public function getDiscussionContext(int $discussionId): array
    {
        $rows = $this->discussionModel->rawQuery(
            "SELECT
                d.id,
                d.cohort_id,
                d.author_id,
                c.title AS cohort_title,
                c.course_id,
                c.program_id,
                p.first_name AS author_first_name,
                p.last_name AS author_last_name
            FROM discussions d
            INNER JOIN program_course_cohorts c
                ON c.id = d.cohort_id
            INNER JOIN program_profiles p
                ON p.id = d.author_id
            WHERE d.id = :discussion_id
            LIMIT 1",
            [
                'discussion_id' => $discussionId,
            ]
        );

        return $rows[0] ?? [];
    }

    public function getPostContext(int $postId): array
    {
        $rows = $this->discussionPostModel->rawQuery(
            "SELECT
                p.id,
                p.parent_post_id,
                p.discussion_id,
                p.author_id,
                p.depth,
                d.cohort_id,
                d.author_id AS discussion_author_id,
                c.course_id,
                c.program_id,
                c.title AS cohort_title,
                author.first_name AS author_first_name,
                author.last_name AS author_last_name,
                parent.author_id AS parent_author_id,
                parent.depth AS parent_depth
            FROM discussion_posts p
            INNER JOIN discussions d
                ON d.id = p.discussion_id
            INNER JOIN program_course_cohorts c
                ON c.id = d.cohort_id
            INNER JOIN program_profiles author
                ON author.id = p.author_id
            LEFT JOIN discussion_posts parent
                ON parent.id = p.parent_post_id
            WHERE p.id = :post_id
            LIMIT 1",
            [
                'post_id' => $postId,
            ]
        );

        return $rows[0] ?? [];
    }

    public function getRecipientsForDiscussionStarted(int $discussionId): array
    {
        return $this->recipientQuery(
            "SELECT DISTINCT
                pp.id AS profile_id,
                pp.user_id
            FROM discussions d
            INNER JOIN program_course_cohort_enrollments e
                ON e.cohort_id = d.cohort_id
            INNER JOIN program_profiles pp
                ON pp.id = e.profile_id
            WHERE d.id = :discussion_id
              AND pp.id <> d.author_id
              AND pp.user_id IS NOT NULL",
            [
                'discussion_id' => $discussionId,
            ]
        );
    }

    public function getDiscussionAuthorRecipientForPost(int $postId): array
    {
        $rows = $this->discussionPostModel->rawQuery(
            "SELECT
                pp.id AS profile_id,
                pp.user_id
            FROM discussion_posts post
            INNER JOIN discussions d
                ON d.id = post.discussion_id
            INNER JOIN program_profiles pp
                ON pp.id = d.author_id
            WHERE post.id = :post_id
              AND d.author_id <> post.author_id
              AND pp.user_id IS NOT NULL
            LIMIT 1",
            [
                'post_id' => $postId,
            ]
        );

        return $rows[0] ?? [];
    }

    public function getParticipantRecipientsForDiscussion(int $postId): array
    {
        return $this->recipientQuery(
            "SELECT DISTINCT
                pp.id AS profile_id,
                pp.user_id
            FROM discussion_posts current_post
            INNER JOIN discussions d
                ON d.id = current_post.discussion_id
            INNER JOIN discussion_posts previous_post
                ON previous_post.discussion_id = current_post.discussion_id
               AND previous_post.id <> current_post.id
            INNER JOIN program_profiles pp
                ON pp.id = previous_post.author_id
            WHERE current_post.id = :post_id
              AND previous_post.author_id <> current_post.author_id
              AND previous_post.author_id <> d.author_id
              AND pp.user_id IS NOT NULL",
            [
                'post_id' => $postId,
            ]
        );
    }

    public function getParentAuthorRecipientForPost(int $postId): array
    {
        $rows = $this->discussionPostModel->rawQuery(
            "SELECT
                pp.id AS profile_id,
                pp.user_id
            FROM discussion_posts p
            INNER JOIN discussion_posts parent
                ON parent.id = p.parent_post_id
            INNER JOIN program_profiles pp
                ON pp.id = parent.author_id
            WHERE p.id = :post_id
              AND parent.author_id <> p.author_id
              AND pp.user_id IS NOT NULL
            LIMIT 1",
            [
                'post_id' => $postId,
            ]
        );

        return $rows[0] ?? [];
    }

    public function sendPushToRecipient(array $recipient, string $title, string $body, array $data): void
    {
        $userId = $recipient['user_id'] ?? null;
        if ($userId === null) {
            return;
        }

        $tokens = $this->userDeviceToken
            ->select(['fcm_token'])
            ->where('user_id', $userId)
            ->get();

        $tokens = array_values(array_filter(array_unique(array_column($tokens, 'fcm_token'))));
        if (empty($tokens)) {
            return;
        }

        $eventKey = $data['event_key'] ?? null;
        foreach ($tokens as $token) {
            if ($this->hasPushBeenSent($token, $title, $body, $eventKey)) {
                continue;
            }

            $this->notificationService->send($token, $title, $body, $data);
        }
    }

    public function sendPushInBatches(
        array $recipients,
        string $title,
        string $body,
        array $data,
        ?int $batchSize = null
    ): void {
        $batchSize = $this->resolveBatchSize($batchSize, 'PUSH_BATCH_SIZE', 300);
        $eventKey = $data['event_key'] ?? null;
        $tokensMap = [];

        foreach ($recipients as $recipient) {
            $userId = $recipient['user_id'] ?? null;
            if ($userId === null) {
                continue;
            }

            $tokens = $this->userDeviceToken
                ->select(['fcm_token'])
                ->where('user_id', $userId)
                ->get();

            foreach ($tokens as $row) {
                $token = $row['fcm_token'] ?? null;
                if ($token === null || $token === '') {
                    continue;
                }

                $tokensMap[$token] = true;
            }
        }

        $pendingTokens = [];
        foreach (array_keys($tokensMap) as $token) {
            if ($this->hasPushBeenSent($token, $title, $body, $eventKey)) {
                continue;
            }

            $pendingTokens[] = $token;
        }

        foreach (array_chunk($pendingTokens, $batchSize) as $chunk) {
            $this->notificationService->sendBatch($chunk, $title, $body, $data);
        }
    }

    public function buildPushData(string $type, array $fields): array
    {
        return array_filter(
            array_merge(['type' => $type], $fields),
            static fn(mixed $value): bool => $value !== null && $value !== ''
        );
    }

    public function buildFullName(?string $firstName, ?string $lastName): ?string
    {
        $fullName = trim((string) $firstName . ' ' . (string) $lastName);

        return $fullName !== '' ? $fullName : null;
    }

    private function recipientQuery(string $sql, array $params): array
    {
        $rows = $this->discussionPostModel->rawQuery($sql, $params);

        return array_values(array_filter($rows, static function (array $row): bool {
            return isset($row['profile_id'], $row['user_id']);
        }));
    }

    private function hasPushBeenSent(string $token, string $title, string $body, ?string $eventKey = null): bool
    {
        if ($eventKey !== null && $this->notificationLogSupportsEventKey()) {
            $rows = $this->notificationLog->rawQuery(
                "
                SELECT 1
                FROM notifications
                WHERE recipient_token = :token
                  AND event_key = :event_key
                LIMIT 1
                ",
                [
                    'token' => $token,
                    'event_key' => $eventKey,
                ]
            );

            return !empty($rows);
        }

        $rows = $this->notificationLog->rawQuery(
            "
            SELECT 1
            FROM notifications
            WHERE recipient_token = :token
              AND title = :title
              AND body = :body
            LIMIT 1
            ",
            [
                'token' => $token,
                'title' => $title,
                'body' => $body,
            ]
        );

        return !empty($rows);
    }

    private function notificationLogSupportsEventKey(): bool
    {
        if ($this->notificationLogSupportsEventKey !== null) {
            return $this->notificationLogSupportsEventKey;
        }

        $rows = $this->notificationLog->rawQuery("SHOW COLUMNS FROM notifications LIKE 'event_key'");
        $this->notificationLogSupportsEventKey = !empty($rows);

        return $this->notificationLogSupportsEventKey;
    }

    private function resolveBatchSize(?int $batchSize, string $envKey, int $default): int
    {
        if ($batchSize !== null && $batchSize > 0) {
            return $batchSize;
        }

        $envValue = getenv($envKey);
        if ($envValue === false) {
            return $default;
        }

        $parsed = filter_var($envValue, FILTER_VALIDATE_INT);
        if ($parsed === false || $parsed <= 0) {
            return $default;
        }

        return $parsed;
    }
}
