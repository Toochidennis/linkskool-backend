<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Models\Common\Notification;
use V3\App\Models\Common\UserDeviceToken;
use V3\App\Models\Explore\CbtUser;
use V3\App\Models\Explore\EmailLog;
use V3\App\Models\Explore\News;
use V3\App\Services\Common\MailService;
use V3\App\Services\Common\NotificationService;

class NewsNotificationTargetService
{
    private News $newsModel;
    private CbtUser $userModel;
    private EmailLog $emailLog;
    private Notification $notificationLog;
    private UserDeviceToken $userDeviceToken;
    private MailService $mailService;
    private NotificationService $notificationService;

    public function __construct(\PDO $pdo)
    {
        $this->newsModel = new News($pdo);
        $this->userModel = new CbtUser($pdo);
        $this->emailLog = new EmailLog($pdo);
        $this->notificationLog = new Notification($pdo);
        $this->userDeviceToken = new UserDeviceToken($pdo);
        $this->mailService = new MailService($pdo);
        $this->notificationService = new NotificationService($pdo);
    }

    public function getNews(int $newsId): array
    {
        return $this->newsModel
            ->where('id', $newsId)
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

    public function sendEmailOnce(
        string $email,
        string $name,
        string $subject,
        string $html
    ): void {
        if ($this->hasEmailBeenSent($email, $subject)) {
            return;
        }

        $this->mailService->send(
            "{$name} <{$email}>",
            $subject,
            $html
        );
    }

    public function sendPushOnce(
        int $userId,
        string $title,
        string $body,
        array $data
    ): void {
        $tokens = $this->userDeviceToken
            ->select(['fcm_token'])
            ->where('user_id', $userId)
            ->get();

        $tokens = array_values(array_filter(array_unique(array_column($tokens, 'fcm_token'))));

        if (empty($tokens)) {
            return;
        }

        foreach ($tokens as $token) {
            if ($this->hasPushBeenSent($token, $title, $body)) {
                continue;
            }

            $this->notificationService->send($token, $title, $body, $data);
        }
    }

    public function sendEmailInBatches(
        array $recipients,
        array $news,
        string $subject,
        ?int $batchSize = null
    ): void {
        $batchSize = $this->resolveBatchSize(
            $batchSize,
            'EMAIL_BATCH_SIZE',
            100
        );

        $batchRecipients = [];

        foreach ($recipients as $recipient) {
            $email = (string)($recipient['email'] ?? '');
            if ($email === '') {
                continue;
            }

            if ($this->hasEmailBeenSent($email, $subject)) {
                continue;
            }

            $name = trim((string)($recipient['name'] ?? '')) ?: 'User';
            $batchRecipients[] = "{$name} <{$email}>";
        }

        if (empty($batchRecipients)) {
            return;
        }

        $html = $this->renderNewsPostedEmail([
            ...$news,
            'recipient_name' => 'User',
        ]);

        foreach (array_chunk($batchRecipients, $batchSize) as $chunk) {
            $this->mailService->sendBatch($chunk, $subject, $html);
        }
    }

    public function sendPushInBatches(
        array $recipients,
        string $title,
        string $body,
        array $data,
        ?int $batchSize = null
    ): void {
        $batchSize = $this->resolveBatchSize(
            $batchSize,
            'PUSH_BATCH_SIZE',
            300
        );

        $tokens = [];

        foreach ($recipients as $recipient) {
            $userId = (int)($recipient['user_id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $userTokens = $this->userDeviceToken
                ->select(['fcm_token'])
                ->where('user_id', $userId)
                ->get();

            foreach ($userTokens as $row) {
                $token = (string)($row['fcm_token'] ?? '');
                if ($token === '') {
                    continue;
                }

                $tokens[$token] = true;
            }
        }

        $tokens = array_keys($tokens);
        if (empty($tokens)) {
            return;
        }

        $pendingTokens = [];
        foreach ($tokens as $token) {
            if ($this->hasPushBeenSent($token, $title, $body)) {
                continue;
            }

            $pendingTokens[] = $token;
        }

        foreach (array_chunk($pendingTokens, $batchSize) as $chunk) {
            $this->notificationService->sendBatch($chunk, $title, $body, $data);
        }
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

    public function renderNewsPostedEmail(array $data): string
    {
        $v3root = realpath(__DIR__ . '/../../');
        if ($v3root === false) {
            return '';
        }

        return TemplateRenderer::render(
            $v3root . '/Templates/emails/news_posted.php',
            $data
        );
    }

    private function hasEmailBeenSent(string $email, string $subject): bool
    {
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
                'recipient' => '%<' . $email . '>%'
            ]
        );

        return !empty($rows);
    }

    private function hasPushBeenSent(string $token, string $title, string $body): bool
    {
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
}
