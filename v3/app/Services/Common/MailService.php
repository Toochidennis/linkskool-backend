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

    public function __construct(\PDO $pdo)
    {
        $this->mailgun = Mailgun::create(getenv('MAILGUN_API_KEY'));
        $this->domain = getenv('MAILGUN_DOMAIN');
        $this->from = getenv('MAIL_FROM');
        $this->emailLog = new EmailLog($pdo);
    }

    public function send(string $to, string $subject, string $html): void
    {
        try {
            $response = $this->mailgun->messages()->send($this->domain, [
                'from' => $this->from,
                'to' => $to,
                'subject' => $subject,
                'html' => $html,
            ]);

            $this->emailLog->insert([
                'recipient' => $to,
                'subject' => $subject,
                'status' => 'sent',
                'response_id' => $response->getId(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            $this->emailLog->insert([
                'recipient' => $to,
                'subject' => $subject,
                'status' => 'failed',
                'response_id' => null,
                'error_message' => $e->getMessage(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
