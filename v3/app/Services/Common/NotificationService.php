<?php

namespace V3\App\Services\Common;

use Google\Auth\Credentials\ServiceAccountCredentials;
use V3\App\Models\Common\Notification;
use V3\App\Models\Common\UserDeviceToken;

class NotificationService
{
    private $projectUrl;
    private Notification $notification;
    private UserDeviceToken $userDeviceToken;
    private ?bool $supportsEventKey = null;

    public function __construct(\PDO $pdo)
    {
        $this->projectUrl = getenv('FIREBASE_PROJECT_URL');
        $this->notification = new Notification($pdo);
        $this->userDeviceToken = new UserDeviceToken($pdo);
    }

    public function send(string $to, string $title, string $body, array $data = []): void
    {
        $eventKey = $data['event_key'] ?? null;
        $payload = $this->buildLogPayload($to, $title, $body, $eventKey);

        try {
            $accessToken = $this->fetchAccessToken();
            $message = $this->buildMessagePayload($to, $title, $body, $data);

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
                if ($this->isTokenUnregistered((string) $response)) {
                    $this->invalidateToken($to);
                    $this->notification->insert([...$payload, 'error_message' => 'Token unregistered — removed.']);
                } else {
                    throw new \RuntimeException("FCM failed with HTTP $httpCode. Response: $response");
                }
            } else {
                $this->notification->insert($payload);
            }
        } catch (\Throwable $e) {
            $this->notification->insert([
                ...$payload,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    public function sendBatch(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_filter(array_unique($tokens)));
        if (empty($tokens)) {
            return;
        }

        $eventKey = $data['event_key'] ?? null;

        try {
            $accessToken = $this->fetchAccessToken();
            $multiHandle = curl_multi_init();
            $handles = [];

            foreach ($tokens as $token) {
                $curl = curl_init($this->projectUrl);
                $message = $this->buildMessagePayload($token, $title, $body, $data);

                curl_setopt_array($curl, [
                    CURLOPT_HTTPHEADER => [
                        "Authorization: Bearer {$accessToken}",
                        'Content-Type: application/json',
                    ],
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($message),
                    CURLOPT_RETURNTRANSFER => true,
                ]);

                curl_multi_add_handle($multiHandle, $curl);
                $handles[(int) $curl] = ['curl' => $curl, 'token' => $token];
            }

            do {
                $status = curl_multi_exec($multiHandle, $active);
                if ($active) {
                    curl_multi_select($multiHandle, 1.0);
                }
            } while ($active && $status === CURLM_OK);

            foreach ($handles as $entry) {
                $curl = $entry['curl'];
                $token = $entry['token'];
                $payload = $this->buildLogPayload($token, $title, $body, $eventKey);

                $response = curl_multi_getcontent($curl);
                $curlError = curl_error($curl);
                $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if ($curlError !== '') {
                    $this->notification->insert([
                        ...$payload,
                        'error_message' => 'Curl error: ' . $curlError,
                    ]);
                } elseif ($httpCode !== 200) {
                    if ($this->isTokenUnregistered((string) $response)) {
                        $this->invalidateToken($token);
                        $this->notification->insert([...$payload, 'error_message' => 'Token unregistered — removed.']);
                    } else {
                        $this->notification->insert([
                            ...$payload,
                            'error_message' => "FCM failed with HTTP {$httpCode}. Response: {$response}",
                        ]);
                    }
                } else {
                    $this->notification->insert($payload);
                }

                curl_multi_remove_handle($multiHandle, $curl);
                curl_close($curl);
            }

            curl_multi_close($multiHandle);
        } catch (\Throwable $e) {
            foreach ($tokens as $token) {
                $payload = $this->buildLogPayload($token, $title, $body, $eventKey);
                $this->notification->insert([
                    ...$payload,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function isTokenUnregistered(string $response): bool
    {
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return false;
        }
        foreach ($decoded['error']['details'] ?? [] as $detail) {
            if (($detail['errorCode'] ?? '') === 'UNREGISTERED') {
                return true;
            }
        }
        return false;
    }

    private function invalidateToken(string $token): void
    {
        try {
            $this->userDeviceToken->where('fcm_token', $token)->delete();
        } catch (\Throwable) {
        }
    }

    private function fetchAccessToken(): string
    {
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

        return $accessToken;
    }

    private function buildMessagePayload(string $to, string $title, string $body, array $data): array
    {
        $normalizedData = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) || $key === '' || $value === null) {
                continue;
            }

            if (is_bool($value)) {
                $normalizedData[$key] = $value ? '1' : '0';
                continue;
            }

            if (is_int($value) || is_float($value) || is_string($value)) {
                $normalizedData[$key] = (string) $value;
            }
        }

        $baseData = [
            'lesson_id' => '',
            'type' => '',
            'cohort_id' => '',
            'profile_id' => '',
            'course_id' => '',
            'program_id' => '',
        ];

        return [
            'message' => [
                'token' => $to,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_merge($baseData, $normalizedData)
            ],
        ];
    }

    private function buildLogPayload(string $to, string $title, string $body, ?string $eventKey = null): array
    {
        $payload = [
            'recipient_token' => $to,
            'title' => $title,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($eventKey !== null && $this->supportsEventKey()) {
            $payload['event_key'] = $eventKey;
        }

        return $payload;
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
