<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CbtUser;
use V3\App\Models\Portal\Payments\Transaction;

class CbtUserService
{
    private CbtUser $user;
    private Transaction $transaction;

    public function __construct(\PDO $pdo)
    {
        $this->user = new CbtUser($pdo);
        $this->transaction = new Transaction($pdo);
    }

    public function createUser(array $data): int
    {
        return $this->user->insert($data);
    }

    public function findOrCreateUserByEmail(array $data): array
    {
        $payload = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'profile_picture' => $data['profile_picture'] ?? null,
            'attempt' => $data['attempt'] ?? 0,
            'phone' => $data['phone'] ?? null,
        ];

        if (isset($data['password'])) {
            $payload['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $user = $this->user
            ->where('email', '=', $payload['email'])
            ->first();

        if ($user) {
            $this->updateUser($user['id'], [
                'first_name' => $payload['first_name'] ?? $user['first_name'],
                'last_name' => $payload['last_name'] ?? $user['last_name'],
            ]);

            return $this->user
                ->where('id', '=', $user['id'])
                ->first();
        }

        try {
            $id = $this->createUser($payload);

            return $this->user
                ->where('id', '=', $id)
                ->first();
        } catch (\PDOException $e) {
            if ($this->isDuplicateEmailError($e)) {
                return $this->getUserByEmail($payload['email']);
            }

            throw $e;
        }
    }

    private function isDuplicateEmailError(\PDOException $e): bool
    {
        return $e->getCode() === '23000';
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
        $user = true;

        if (!$verification['success']) {
            $status = 0;
            $description = 'Payment verification failed: ' . $verification['message'];
        } else {
            if ($verification['amount'] != $data['amount']) {
                $status = 0;
                $description = 'Payment amount mismatch';
            } elseif (\in_array($verification['status'], ['failed', 'abandoned'])) {
                $status = 0;
                $description = 'Payment was not successful: ' . $verification['status'];
            } elseif ($verification['status'] === 'pending') {
                $status = 2;
                $description = 'Payment is still pending';
                $_SESSION['pending_payments'][$data['reference']] = [
                    'name' => $data['name'],
                    'id' => $data['id'],
                    'amount' => $data['amount'],
                    'subscription_type' => $data['subscription_type'] ?? 'annual',
                ];
            } else {
                $expireDate = match ($data['subscription_type'] ?? 'annual') {
                    'monthly' => date('Y-m-d H:i:s', strtotime('+1 month')),
                    'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months')),
                    default => date('Y-m-d H:i:s', strtotime('+1 year')),
                };

                $payload = [
                    'subscribed' => 1,
                    'date_subscribed' => date('Y-m-d H:i:s'),
                    'expiry_date' => $expireDate,
                    'amount' => $verification['amount'],
                    'subscription_type' => $data['subscription_type'] ?? 'annual',
                ];

                $user = $this->user
                    ->where('id', '=', $data['id'])
                    ->update($payload);

                $status = $user ? 1 : 0;
                $description = $user
                    ? 'Payment verification completed successfully'
                    : 'Payment succeeded but user subscription update failed';
            }
        }

        if ($status === 1 || $status === 2) {
            $receiptPayload =  [
                'trans_type' => 'receipts',
                'memo' => $description,
                'c_type' => 1,
                'ref' => $data['reference'],
                'cid' => $data['id'],
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

        return false;
    }

    private function verifyPayment(string $reference): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . getenv('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            return [
                'success' => false,
                'message' => "Network error: {$err}",
                'status' => null,
                'amount' => 0
            ];
        }

        $result = json_decode($response, true);

        if (!isset($result['status']) || $result['status'] !== true) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Verification failed',
                'status' => null,
                'amount' => 0
            ];
        }

        return [
            'success' => true,
            'message' => 'OK',
            'status' => $result['data']['status'],
            'amount' => $result['data']['amount'] / 100
        ];
    }

    // public function retryVerifyPayment(string $reference): array
    // {
    //     if(!isset($_SESSION['pending_payments'][$reference])) {
    //         return [
    //             'success' => false,
    //             'message' => 'No pending payment found for the given reference.'
    //         ];
    //     }

    //     return $this->verifyPayment($reference);
    // }

    public function getUserByEmail(string $email): array
    {
        return $this->user
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
