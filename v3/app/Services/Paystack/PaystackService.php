<?php

namespace V3\App\Services\Paystack;

final class PaystackService
{
    public function verify(string $reference): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => getenv('PAYSTACK_VERIFY_URL') . "/{$reference}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . getenv('PAYSTACK_SECRET_KEY'),
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
            'currency' => $data['data']['currency'],
            'message' => $data['message'],
            'raw' => $data,
        ];
    }
}
