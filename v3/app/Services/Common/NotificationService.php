<?php

namespace V3\App\Services\Common;

use Google\Auth\Credentials\ServiceAccountCredentials;
use V3\App\Models\Common\Notification;

class NotificationService
{
    private $projectUrl;
    private Notification $notification;
    private ?bool $supportsEventKey = null;

    public function __construct(\PDO $pdo)
    {
        $this->projectUrl = getenv('FIREBASE_PROJECT_URL');
        $this->notification = new Notification($pdo);
    }

    public function send(string $to, string $title, string $body, array $data = []): void
    {
        $payload = [
            'recipient_token' => $to,
            'title' => $title,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $eventKey = $data['event_key'] ?? null;
        if ($eventKey !== null && $this->supportsEventKey()) {
            $payload['event_key'] = $eventKey;
        }

        try {
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

            $credentials = new ServiceAccountCredentials(
                $scopes,
                json_decode(
                    file_get_contents(__DIR__ . '/../../../config/firebase-service-account.json'),
                    true
                )
            );

            $accessTokenData = $credentials->fetchAuthToken();
            $accessToken = $accessTokenData['access_token'] ?? null;

            if (!$accessToken) {
                throw new \RuntimeException('Failed to retrieve Firebase access token.');
            }

            $message = [
                'message' => [
                    'token' => $to,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => [
                        'lesson_id' => $data['lesson_id'] ?? '',
                        'type' => $data['type'],
                        'cohort_id' => $data['cohort_id'] ?? '',
                        'profile_id' => $data['profile_id'] ?? '',
                        'course_id' => $data['course_id'] ?? '',
                        'program_id' => $data['program_id'] ?? '',
                    ]
                ],
            ];

            $curl = curl_init($this->projectUrl);

            curl_setopt_array($curl, [
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$accessToken}",
                    'Content-Type: application/json',
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($message),
                CURLOPT_RETURNTRANSFER => true,
            ]);

            $response = curl_exec($curl);

            if ($response === false) {
                throw new \RuntimeException('Curl error: ' . curl_error($curl));
            }

            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode !== 200) {
                throw new \RuntimeException("FCM failed with HTTP $httpCode. Response: $response");
            }

            $this->notification->insert($payload);
        } catch (\Throwable $e) {
            $this->notification->insert([
                ...$payload,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function supportsEventKey(): bool
    {
        if ($this->supportsEventKey !== null) {
            return $this->supportsEventKey;
        }

        $rows = $this->notification->rawQuery("SHOW COLUMNS FROM notifications LIKE 'event_key'");
        $this->supportsEventKey = !empty($rows);

        return $this->supportsEventKey;
    }
}
