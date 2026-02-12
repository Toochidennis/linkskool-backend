<?php

namespace V3\App\Services\Common;

use Mailgun\Mailgun;

class MailService
{
    private Mailgun $mailgun;
    private string $domain;
    private string $from;

    public function __construct()
    {
        $this->mailgun = Mailgun::create(getenv('MAILGUN_API_KEY'));
        $this->domain = getenv('MAILGUN_DOMAIN');
        $this->from = getenv('MAIL_FROM');
    }

    public function send(string $to, string $subject, string $html): void
    {
        try {
            $this->mailgun->messages()->send($this->domain, [
                'from' => $this->from,
                'to' => $to,
                'subject' => $subject,
                'html' => $html,
            ]);
        } catch (\Throwable $e) {
            // log it. Do NOT crash grading flow.
            error_log("Mail failed: " . $e->getMessage());
        }
    }
}
