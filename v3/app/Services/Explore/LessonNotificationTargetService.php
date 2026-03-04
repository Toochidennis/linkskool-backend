<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Models\Common\Notification;
use V3\App\Models\Common\UserDeviceToken;
use V3\App\Models\Explore\EmailLog;
use V3\App\Models\Explore\ProgramCourseCohortLesson;
use V3\App\Services\Common\MailService;
use V3\App\Services\Common\NotificationService;

class LessonNotificationTargetService
{
    private ProgramCourseCohortLesson $lessonModel;
    private EmailLog $emailLog;
    private Notification $notificationLog;
    private UserDeviceToken $userDeviceToken;
    private MailService $mailService;
    private NotificationService $notificationService;
    private ?bool $emailLogSupportsEventKey = null;
    private ?bool $notificationLogSupportsEventKey = null;

    public function __construct(\PDO $pdo)
    {
        $this->lessonModel = new ProgramCourseCohortLesson($pdo);
        $this->emailLog = new EmailLog($pdo);
        $this->notificationLog = new Notification($pdo);
        $this->userDeviceToken = new UserDeviceToken($pdo);
        $this->mailService = new MailService($pdo);
        $this->notificationService = new NotificationService($pdo);
    }

    public function getLesson(int $lessonId): array
    {
        return $this->lessonModel
            ->where('id', $lessonId)
            ->first();
    }

    public function getRecipientsForLesson(int $lessonId): array
    {
        $sql = "
            SELECT DISTINCT
                e.profile_id,
                p.user_id,
                p.first_name,
                p.last_name,
                u.email
            FROM program_course_cohort_lessons l
            INNER JOIN program_course_cohort_enrollments e
                ON e.cohort_id = l.cohort_id
            INNER JOIN program_profiles p
                ON p.id = e.profile_id
            INNER JOIN cbt_users u
                ON u.id = p.user_id
            WHERE l.id = :lesson_id
                AND (e.status IS NULL OR e.status <> 'completed')
        ";

        $rows = $this->lessonModel->rawQuery($sql, ['lesson_id' => $lessonId]);

        return array_values(array_filter($rows, function (array $row): bool {
            return !empty($row['user_id']);
        }));
    }

    public function shouldSendAssignmentReminder(int $lessonId, int $profileId): bool
    {
        $sql = "
            SELECT
                EXISTS (
                    SELECT 1
                    FROM cohort_lesson_quizzes q
                    WHERE q.lesson_id = l.id
                ) AS has_quiz,
                s.submission_type,
                s.text_content,
                s.link_url,
                s.files,
                s.quiz_score
            FROM program_course_cohort_lessons l
            LEFT JOIN cohort_tasks_submissions s
                ON s.id = (
                    SELECT s2.id
                    FROM cohort_tasks_submissions s2
                    WHERE s2.lesson_id = l.id
                        AND s2.profile_id = :profile_id
                    ORDER BY s2.id DESC
                    LIMIT 1
                )
            WHERE l.id = :lesson_id
            LIMIT 1
        ";

        $rows = $this->lessonModel->rawQuery($sql, [
            'lesson_id' => $lessonId,
            'profile_id' => $profileId,
        ]);

        if (empty($rows)) {
            return false;
        }

        $row = $rows[0];
        $hasQuiz = (bool) ($row['has_quiz'] ?? false);
        $quizDone = !$hasQuiz || ($row['quiz_score'] !== null);
        $assignmentDone = $this->isAssignmentDone($row);

        return !($quizDone && $assignmentDone);
    }

    public function sendEmailOnce(
        string $email,
        string $name,
        string $subject,
        string $html,
        ?string $eventKey = null
    ): void {
        if ($this->hasEmailBeenSent($email, $subject, $eventKey)) {
            return;
        }

        $this->mailService->send(
            "{$name} <{$email}>",
            $subject,
            $html,
            $eventKey
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
            if ($this->hasPushBeenSent($token, $title, $body, $data['event_key'] ?? null)) {
                continue;
            }

            $this->notificationService->send($token, $title, $body, $data);
        }
    }

    public function renderClassReminderEmail(array $data): string
    {
        $v3root = realpath(__DIR__ . '/../../');
        if ($v3root === false) {
            return '';
        }

        return TemplateRenderer::render(
            $v3root . '/Templates/emails/lesson_class_reminder.php',
            $data
        );
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
                'recipient' => '%<' . $email . '>%'
            ]
        );

        return !empty($rows);
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

    private function isAssignmentDone(array $row): bool
    {
        if ($this->hasNonEmptyValue($row['submission_type'] ?? null)) {
            return true;
        }

        if ($this->hasNonEmptyValue($row['text_content'] ?? null)) {
            return true;
        }

        if ($this->hasNonEmptyValue($row['link_url'] ?? null)) {
            return true;
        }

        if (!$this->hasNonEmptyValue($row['files'] ?? null)) {
            return false;
        }

        $files = json_decode((string) $row['files'], true);
        return \is_array($files) && !empty($files);
    }

    private function hasNonEmptyValue(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }
}
