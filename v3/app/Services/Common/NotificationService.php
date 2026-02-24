<?php

namespace V3\App\Services\Common;

use Google\Auth\Credentials\ServiceAccountCredentials;
use V3\App\Models\Common\Notification;

class NotificationService
{
    private $messagingUrl;
    private $projectUrl;
    private Notification $notification;

    public function __construct(\PDO $pdo)
    {
        $this->messagingUrl = $_ENV['FIREBASE_MESSAGING_URL'];
        $this->projectUrl = $_ENV['FIREBASE_PROJECT_URL'];
        $this->notification = new Notification($pdo);
    }

    public function send(string $to, string $title, string $body): void
    {
        try {
            $scopes = [$this->messagingUrl];

            $credentials = new ServiceAccountCredentials(
                $scopes,
                json_decode(file_get_contents(__DIR__ . '/../../../config/firebase-service-account.json'), true)
            );

            $accessToken = $credentials->fetchAuthToken();

            $message = [
                'message' => [
                    'token' => $to,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ];

            $curl = curl_init($this->projectUrl);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $accessToken",
                'Content-Type: application/json',
            ]);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_exec($curl);
            curl_close($curl);

            $this->notification->insert([
                'recipient_token' => $to,
                'title' => $title,
                'body' => $body,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            $this->notification->insert([
                'recipient_token' => $to,
                'title' => $title,
                'body' => $body,
                'error_message' => $e->getMessage(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
