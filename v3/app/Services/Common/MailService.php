<?php

namespace V3\App\Services\Common;

use Mailgun\Mailgun;
use V3\App\Models\Explore\EmailLog;

class MailService
{
    private Mailgun $mailgun;
    private string $domain;
    private string $from;
    private EmailLog $emailLog;
    private ?bool $supportsEventKey = null;

    public function __construct(\PDO $pdo, ?string $from = null)
    {
        $this->mailgun = Mailgun::create(getenv('MAILGUN_API_KEY'));
        $this->domain = getenv('MAILGUN_DOMAIN');
        $this->from = $from ?? getenv('MAIL_FROM');
        $this->emailLog = new EmailLog($pdo);
    }

    public function send(string $to, string $subject, string $html, ?string $eventKey = null): void
    {
        $payload = [
            'recipient' => $to,
            'subject' => $subject,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($eventKey !== null && $this->supportsEventKey()) {
            $payload['event_key'] = $eventKey;
        }

        try {
            $response = $this->mailgun->messages()->send($this->domain, [
                'from' => $this->from,
                'to' => $to,
                'subject' => $subject,
                'html' => $html,
            ]);

            $this->emailLog->insert([
                ...$payload,
                'status' => 'sent',
                'response_id' => $response->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->emailLog->insert([
                ...$payload,
                'status' => 'failed',
                'response_id' => null,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    public function sendBatch(array $to, string $subject, string $html, ?string $eventKey = null): void
    {
        $recipients = array_values(array_filter(array_unique($to)));
        if (empty($recipients)) {
            return;
        }

        $basePayload = [
            'subject' => $subject,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($eventKey !== null && $this->supportsEventKey()) {
            $basePayload['event_key'] = $eventKey;
        }

        try {
            $response = $this->mailgun->messages()->send($this->domain, [
                'from' => $this->from,
                'to' => $recipients,
                'subject' => $subject,
                'html' => $html,
            ]);

            foreach ($recipients as $recipient) {
                $this->emailLog->insert([
                    ...$basePayload,
                    'recipient' => $recipient,
                    'status' => 'sent',
                    'response_id' => $response->getId(),
                ]);
            }
        } catch (\Throwable $e) {
            foreach ($recipients as $recipient) {
                $this->emailLog->insert([
                    ...$basePayload,
                    'recipient' => $recipient,
                    'status' => 'failed',
                    'response_id' => null,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function supportsEventKey(): bool
    {
        if ($this->supportsEventKey !== null) {
            return $this->supportsEventKey;
        }

        $rows = $this->emailLog->rawQuery("SHOW COLUMNS FROM email_logs LIKE 'event_key'");
        $this->supportsEventKey = !empty($rows);

        return $this->supportsEventKey;
    }
}
