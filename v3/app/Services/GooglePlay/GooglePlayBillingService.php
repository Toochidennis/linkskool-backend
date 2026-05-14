<?php

namespace V3\App\Services\GooglePlay;

use Google\Auth\Credentials\ServiceAccountCredentials;

final class GooglePlayBillingService
{
    private const PACKAGE_NAME = 'com.linkskool.app';
    private const SCOPE = 'https://www.googleapis.com/auth/androidpublisher';
    private const BASE_URL = 'https://androidpublisher.googleapis.com/androidpublisher/v3/applications';

    public function verifySubscription(string $purchaseToken, string $subscriptionId): array
    {
        $accessToken = $this->fetchAccessToken();

        $url = sprintf(
            '%s/%s/purchases/subscriptions/%s/tokens/%s',
            self::BASE_URL,
            self::PACKAGE_NAME,
            urlencode($subscriptionId),
            urlencode($purchaseToken)
        );

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new \RuntimeException('Purchase verification request failed: ' . curl_error($curl));
        }

        curl_close($curl);

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMessage = $data['error']['message'] ?? 'Unknown error';
            throw new \RuntimeException('Purchase verification failed (' . $httpCode . '): ' . $errorMessage);
        }

        // paymentState: 0=pending, 1=received, 2=free trial, 3=pending deferred upgrade
        $paymentState = isset($data['paymentState']) ? (int) $data['paymentState'] : -1;
        $isPaid = \in_array($paymentState, [1, 2], true);

        return [
            'success' => $isPaid,
            'payment_state' => $paymentState,
            'order_id' => (string) ($data['orderId'] ?? ''),
            'expiry_time_millis' => (string) ($data['expiryTimeMillis'] ?? ''),
            'raw' => $data,
        ];
    }

    private function fetchAccessToken(): string
    {
        $credentials = new ServiceAccountCredentials(
            self::SCOPE,
            json_decode(
                file_get_contents(__DIR__ . '/../../../config/billing-service-account.json'),
                true
            )
        );

        $tokenData = $credentials->fetchAuthToken();
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            throw new \RuntimeException('Failed to retrieve billing access token.');
        }

        return $accessToken;
    }
}
