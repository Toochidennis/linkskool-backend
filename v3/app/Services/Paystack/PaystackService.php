<?php

namespace V3\App\Services\Paystack;

final class PaystackService
{
    private function getSecretKey(): string
    {
        return getenv('APP_ENV') === 'production'
            ? (string) getenv('PAYSTACK_PROD_SECRET_KEY')
            : (string) getenv('PAYSTACK_DEV_SECRET_KEY');
    }

    public function initialize(array $payload): array
    {
        $url = getenv('PAYSTACK_INITIALIZE_URL');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->getSecretKey(),
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \RuntimeException(curl_error($curl));
        }

        $data = json_decode($response, true);

        if (!\is_array($data) || empty($data['status'])) {
            throw new \RuntimeException($data['message'] ?? 'Payment initialization failed');
        }

        return [
            'success' => true,
            'message' => $data['message'] ?? 'Initialized',
            'authorization_url' => $data['data']['authorization_url'] ?? null,
            'access_code' => $data['data']['access_code'] ?? null,
            'reference' => $data['data']['reference'] ?? null,
            'raw' => $data,
        ];
    }

    public function verify(string $reference): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => getenv('PAYSTACK_VERIFY_URL') . "/{$reference}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->getSecretKey(),
            ],
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \RuntimeException(curl_error($curl));
        }

        $data = json_decode($response, true);

        if (!$data['status']) {
            throw new \RuntimeException($data['message'] ?? 'Verification failed');
        }

        return [
            'success' => $data['status'],
            'status' => $data['data']['status'], // success | failed | abandoned | pending
            'amount' => $data['data']['amount'] / 100, // Convert kobo to Naira
            'amount_kobo' => (int) $data['data']['amount'],
            'currency' => $data['data']['currency'],
            'message' => $data['message'],
            'raw' => $data,
        ];
    }

    public function isValidWebhookSignature(string $payload, ?string $signature): bool
    {
        if ($payload === '' || empty($signature)) {
            return false;
        }

        $computed = hash_hmac('sha512', $payload, $this->getSecretKey());
        return hash_equals($computed, $signature);
    }
}
