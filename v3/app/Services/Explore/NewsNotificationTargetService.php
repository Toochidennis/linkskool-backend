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
        $rows = $this->userModel->rawQuery(
            "
            SELECT id AS user_id, name, email
            FROM cbt_users
            "
        );

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
