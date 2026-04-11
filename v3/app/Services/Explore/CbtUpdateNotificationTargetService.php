<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Models\Common\Notification;
use V3\App\Models\Common\UserDeviceToken;
use V3\App\Models\Explore\CbtUpdate;
use V3\App\Models\Explore\CbtUser;
use V3\App\Models\Explore\EmailLog;
use V3\App\Services\Common\MailService;
use V3\App\Services\Common\NotificationService;

class CbtUpdateNotificationTargetService
{
    private CbtUpdate $cbtUpdateModel;
    private CbtUser $userModel;
    private EmailLog $emailLog;
    private Notification $notificationLog;
    private UserDeviceToken $userDeviceToken;
    private MailService $mailService;
    private NotificationService $notificationService;
    private ?bool $emailLogSupportsEventKey = null;
    private ?bool $notificationLogSupportsEventKey = null;

    public function __construct(\PDO $pdo)
    {
        $this->cbtUpdateModel = new CbtUpdate($pdo);
        $this->userModel = new CbtUser($pdo);
        $this->emailLog = new EmailLog($pdo);
        $this->notificationLog = new Notification($pdo);
        $this->userDeviceToken = new UserDeviceToken($pdo);
        $this->mailService = new MailService($pdo);
        $this->notificationService = new NotificationService($pdo);
    }

    public function getUpdate(int $updateId): array
    {
        return $this->cbtUpdateModel
            ->where('id', $updateId)
            ->first();
    }

    public function getRecipients(): array
    {
        $rows = $this->userModel
            ->select(['id AS user_id', 'name', 'email'])
            ->get();

        return array_values(array_filter($rows, function (array $row): bool {
            return !empty($row['user_id']);
        }));
    }

    public function isPublished(array $update): bool
    {
        return (string) ($update['status'] ?? '') === 'published';
    }

    public function shouldSendEmail(array $update): bool
    {
        return $this->normalizeBoolean($update['send_email'] ?? false)
            && trim((string) ($update['email_body'] ?? '')) !== '';
    }

    public function shouldSendPush(array $update): bool
    {
        return $this->normalizeBoolean($update['send_push'] ?? false)
            && trim((string) ($update['notification_body'] ?? '')) !== '';
    }

    public function sendEmailInBatches(
        array $recipients,
        array $update,
        string $subject,
        ?string $eventKey = null,
        ?int $batchSize = null
    ): void {
        $batchSize = $this->resolveBatchSize($batchSize, 'EMAIL_BATCH_SIZE', 100);
        $batchRecipients = [];

        foreach ($recipients as $recipient) {
            $email = trim((string) ($recipient['email'] ?? ''));
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            if ($this->hasEmailBeenSent($email, $subject, $eventKey)) {
                continue;
            }

            $name = trim((string) ($recipient['name'] ?? '')) ?: 'Learner';
            $batchRecipients[] = "{$name} <{$email}>";
        }

        if (empty($batchRecipients)) {
            return;
        }

        $html = $this->renderEmail($update);

        foreach (array_chunk($batchRecipients, $batchSize) as $chunk) {
            $this->mailService->sendBatch($chunk, $subject, $html, $eventKey);
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
            $userId = (int) ($recipient['user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $tokens = $this->userDeviceToken
                ->select(['fcm_token'])
                ->where('user_id', $userId)
                ->get();

            foreach ($tokens as $row) {
                $token = trim((string) ($row['fcm_token'] ?? ''));
                if ($token === '') {
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

    public function renderEmail(array $update): string
    {
        $v3root = realpath(__DIR__ . '/../../');
        if ($v3root === false) {
            return '';
        }

        return TemplateRenderer::render(
            $v3root . '/Templates/emails/cbt_update.php',
            $update
        );
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

    private function hasEmailBeenSent(string $email, string $subject, ?string $eventKey = null): bool
    {
        if ($eventKey !== null && $this->emailLogSupportsEventKey()) {
            $rows = $this->emailLog->rawQuery(
                "
                SELECT 1
                FROM email_logs
                WHERE event_key = :event_key
                  AND recipient LIKE :recipient
                LIMIT 1
                ",
                [
                    'event_key' => $eventKey,
                    'recipient' => '%<' . $email . '>%',
                ]
            );

            return !empty($rows);
        }

        $rows = $this->emailLog->rawQuery(
            "
            SELECT 1
            FROM email_logs
            WHERE subject = :subject
              AND recipient LIKE :recipient
            LIMIT 1
            ",
            [
                'subject' => $subject,
                'recipient' => '%<' . $email . '>%',
            ]
        );

        return !empty($rows);
    }

    private function hasPushBeenSent(
        string $token,
        string $title,
        string $body,
        ?string $eventKey = null
    ): bool {
        if ($eventKey !== null && $this->notificationLogSupportsEventKey()) {
            $rows = $this->notificationLog->rawQuery(
                "
                SELECT 1
                FROM notifications
                WHERE event_key = :event_key
                  AND recipient_token = :recipient_token
                LIMIT 1
                ",
                [
                    'event_key' => $eventKey,
                    'recipient_token' => $token,
                ]
            );

            return !empty($rows);
        }

        $rows = $this->notificationLog->rawQuery(
            "
            SELECT 1
            FROM notifications
            WHERE recipient_token = :recipient_token
              AND title = :title
              AND body = :body
            LIMIT 1
            ",
            [
                'recipient_token' => $token,
                'title' => $title,
                'body' => $body,
            ]
        );

        return !empty($rows);
    }

    private function emailLogSupportsEventKey(): bool
    {
        if ($this->emailLogSupportsEventKey !== null) {
            return $this->emailLogSupportsEventKey;
        }

        $rows = $this->emailLog->rawQuery("SHOW COLUMNS FROM email_logs LIKE 'event_key'");
        $this->emailLogSupportsEventKey = !empty($rows);

        return $this->emailLogSupportsEventKey;
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

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        return in_array(
            strtolower(trim((string) $value)),
            ['1', 'true', 'yes', 'on'],
            true
        );
    }
}
