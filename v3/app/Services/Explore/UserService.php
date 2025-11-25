<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\User;
use V3\App\Models\Portal\Payments\Transaction;

class UserService
{
    private User $user;
    private Transaction $transaction;

    public function __construct(\PDO $pdo)
    {
        $this->user = new User($pdo);
        $this->transaction = new Transaction($pdo);
    }

    public function createUser(array $data): int
    {
        return $this->user->insert($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        unset($data['id']);
        return $this->user
            ->where('id', '=', $id)
            ->update($data);
    }

    public function updatePaymentStatus(array $data): bool
    {
        $verification = $this->verifyPayment($data['reference']);
        $status = 1; // Default to success
        $description = 'Payment verification completed successfully';

        if (!$verification['success']) {
            $status = 0;
            $description = 'Payment verification failed: ' . $verification['message'];
            return false;
        }

        if ($verification['status'] !== 'success') {
            $status = 0;
            $description = 'Payment not successful';
            return false;
        }

        $expireDate = match ($data['subscription_type'] ?? 'annual') {
            'monthly' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'quarterly' => date('Y-m-d H:i:s', strtotime('+6 months')),
            default => date('Y-m-d H:i:s', strtotime('+1 year')),
        };

        $payload = [
            'subscribed' => 1,
            'date_subscribed' => date('Y-m-d H:i:s'),
            'expiry_date' => $expireDate,
            'amount_paid' => $verification['amount'],
            'subscription_type' => $data['subscription_type'] ?? 'annual',
        ];

        $user = $this->user
            ->where('id', '=', $data['id'])
            ->update($payload);

        $receiptPayload =  [
            'trans_type' => 'receipts',
            'memo' => $description,
            'c_type' => 1,
            'ref' => $data['reference'],
            'cid' => $data['student_id'],
            'cref' => $data['id'],
            'name' => $data['name'],
            'quantity' => 1,
            'it_id' => 1,
            'amount' => $data['amount'],
            'date' => date('Y-m-d'),
            'account' => 1980,
            'account_name' => 'Income',
            'approved' => 1,
            'status' => $status,
            'sub' => 0
        ];

        $receipt =  $this->transaction->insert($receiptPayload);

        return $user && $receipt;
    }

    private function verifyPayment(string $reference): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . getenv('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return [
                'status' => false,
                'message' => "cURL Error #: {$err}"
            ];
        }

        $result = json_decode($response, true);

        if (!$result['status']) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Verification failed',
                'status' => null
            ];
        }

        return [
            'success' => true,
            'status' => $result['data']['status'],
            'amount' => $result['data']['amount'] / 100, // Convert to standard currency format
        ];
    }

    public function getUserByEmail(string $email): array
    {
        return $this->user
            ->select(['*'])
            ->where('email', '=', $email)
            ->first();
    }

    public function deleteUser(int $id): bool
    {
        return $this->user
            ->where('id', '=', $id)
            ->delete();
    }
}
