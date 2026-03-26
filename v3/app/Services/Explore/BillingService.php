<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Payment;
use V3\App\Models\Explore\Voucher;
use V3\App\Services\Paystack\PaystackService;
use V3\App\Models\Explore\Plan;

class BillingService
{
    private const PAYMENT_EXPIRY_MINUTES = 10;

    private Payment $payment;
    private Voucher $voucher;
    private Plan $plan;

    public function __construct(\PDO $pdo)
    {
        $this->payment = new Payment($pdo);
        $this->voucher = new Voucher($pdo);
        $this->plan = new Plan($pdo);
    }

    public function verify(array $data)
    {
        if ($data['method'] === 'online') {
            return $this->verifyPaymentOnline($data);
        } elseif ($data['method'] === 'voucher') {
            return $this->redeemVoucher($data);
        } else {
            return [
                'status' => 'failed',
                'message' => 'Invalid payment method',
            ];
        }
    }

    public function initiate(array $data): array
    {
        if ($data['method'] !== 'online') {
            return [
                'status' => 'failed',
                'message' => 'Only online payment can be initiated.',
            ];
        }

        return $this->initiatePaymentOnline($data);
    }

    public function handlePaystackWebhook(array $payload, ?string $signature, string $rawBody): array
    {
        $paystack = new PaystackService();

        if (!$paystack->isValidWebhookSignature($rawBody, $signature)) {
            return [
                'status' => 'failed',
                'message' => 'Invalid webhook signature.',
            ];
        }

        if (!\in_array(($payload['event'] ?? ''), ['transaction.success', 'charge.success'], true)) {
            return [
                'status' => 'ignored',
                'message' => 'Event ignored.',
            ];
        }

        $reference = trim((string) ($payload['data']['reference'] ?? ''));

        if ($reference === '') {
            return [
                'status' => 'failed',
                'message' => 'Missing payment reference.',
            ];
        }

        return $this->verifyPaymentByReference($reference);
    }

    private function initiatePaymentOnline(array $data): array
    {
        $paystack = new PaystackService();
        $reference = 'CBT-' . date('YmdHis') . '-' . bin2hex(random_bytes(5));
        $amount = $this->computePrice((int) $data['plan_id']);
        $payload = [
            'email' => $data['email'],
            'amount' => $amount,
            'reference' => $reference,
            'metadata' => [
                'user_id' => (int) $data['user_id'],
                'plan_id' => (int) $data['plan_id'],
                'platform' => (string) $data['platform'],
                'first_name' => (string) $data['first_name'],
                'last_name' => (string) $data['last_name'],
                'payment_type' => 'cbt',
            ],
        ];

        if (!empty($data['callback_url'])) {
            $payload['callback_url'] = $data['callback_url'];
        }

        try {
            $initialized = $paystack->initialize($payload);
            $resolvedReference = (string) ($initialized['reference'] ?? $reference);
            $pendingPayload = [
                'user_id' => $data['user_id'],
                'amount' => $amount,
                'plan_id' => $data['plan_id'],
                'reference' => $resolvedReference,
                'status' => 'pending',
                'message' => 'Payment initialized, awaiting webhook verification.',
                'method' => $data['method'],
                'platform' => $data['platform'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'expires_at' => $this->computePendingPaymentExpiryAt(),
            ];

            $existing = $this->payment
                ->where('reference', $resolvedReference)
                ->first();

            if (!empty($existing)) {
                $saved = $this->payment
                    ->where('reference', $resolvedReference)
                    ->update($pendingPayload);
            } else {
                $saved = $this->payment->insert($pendingPayload);
            }

            if (!$saved) {
                return [
                    'status' => 'failed',
                    'message' => 'Failed to create pending payment record.',
                ];
            }

            return [
                'status' => 'pending',
                'message' => 'Payment initialized successfully.',
                'payment_url' => $initialized['authorization_url'],
                'reference' => $resolvedReference,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'failed',
                'message' => 'Failed to initialize payment: ' . $e->getMessage(),
            ];
        }
    }

    private function verifyPaymentOnline(array $data)
    {
        $paystack = new PaystackService();
        try {
            $verification = $paystack->verify($data['reference']);
        } catch (\Throwable $e) {
            return [
                'status' => 'failed',
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }

        if (!$verification['success']) {
            return [
                'status' => 'failed',
                'message' => 'Payment verification failed: ' . $verification['message'],
            ];
        }

        if (($verification['status'] ?? '') !== 'success') {
            return [
                'status' => 'failed',
                'message' => 'Payment is not successful: ' . ($verification['status'] ?? 'unknown'),
            ];
        }

        $expected = $this->computePrice($data['plan_id']);
        $amountPaid = (int) ($verification['amount_kobo'] ?? ((float) $verification['amount'] * 100));

        if ($amountPaid !== $expected) {
            return [
                'status' => 'failed',
                'message' => "Payment amount mismatch: expected $expected, got $amountPaid",
            ];
        }

        $existingPayment = $this->payment
            ->where('reference', $data['reference'])
            ->first();

        if (
            !empty($existingPayment) &&
            \in_array($existingPayment['status'] ?? '', ['success'], true)
        ) {
            return [
                'status' => 'success',
                'message' => 'Payment already recorded'
            ];
        }

        $successPayload = [
            'user_id' => $data['user_id'],
            'amount' => $amountPaid,
            'plan_id' => $data['plan_id'],
            'reference' => $data['reference'],
            'status' => 'success',
            'message' => 'Payment verified successfully',
            'method' => $data['method'],
            'platform' => $data['platform'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ];

        $paymentId = (!empty($existingPayment)) ? $this->payment
            ->where('reference', $data['reference'])
            ->update($successPayload) : $this->payment->insert($successPayload);

        if ($paymentId) {
            return [
                'status' => 'success',
                'message' => 'Payment verified successfully',
                'salt' => bin2hex(random_bytes(16)) // generate a random salt for license generation
            ];
        }

        return [
            'status' => 'failed',
            'message' => 'Failed to record payment'
        ];
    }

    public function verifyPaymentByReference(string $reference): array
    {
        $existingPayment = $this->payment
            ->where('reference', $reference)
            ->first();

        if (empty($existingPayment)) {
            return [
                'status' => 'failed',
                'message' => 'Payment not found.',
            ];
        }

        if (($existingPayment['status'] ?? '') === 'success') {
            return [
                'status' => 'success',
                'message' => 'Payment already recorded',
                'user_id' => (int) $existingPayment['user_id'],
                'plan_id' => (int) $existingPayment['plan_id'],
                'platform' => (string) $existingPayment['platform'],
                'method' => (string) $existingPayment['method'],
                'reference' => (string) $existingPayment['reference'],
            ];
        }

        $paystack = new PaystackService();

        try {
            $verification = $paystack->verify($reference);
        } catch (\Throwable $e) {
            return [
                'status' => 'failed',
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }

        if (!$verification['success']) {
            return [
                'status' => 'failed',
                'message' => 'Payment verification failed: ' . $verification['message'],
            ];
        }

        if (($verification['status'] ?? '') !== 'success') {
            return [
                'status' => 'failed',
                'message' => 'Payment is not successful: ' . ($verification['status'] ?? 'unknown'),
            ];
        }

        $expected = $this->computePrice((int) $existingPayment['plan_id']);
        $amountPaid = (int) ($verification['amount_kobo'] ?? ((float) $verification['amount'] * 100));

        if ($amountPaid !== $expected) {
            return [
                'status' => 'failed',
                'message' => "Payment amount mismatch: expected $expected, got $amountPaid",
            ];
        }

        $successPayload = [
            'user_id' => (int) $existingPayment['user_id'],
            'amount' => $amountPaid,
            'plan_id' => (int) $existingPayment['plan_id'],
            'reference' => $reference,
            'status' => 'success',
            'message' => 'Payment verified successfully',
            'method' => (string) $existingPayment['method'],
            'platform' => (string) $existingPayment['platform'],
            'first_name' => (string) ($existingPayment['first_name']),
            'last_name' => (string) ($existingPayment['last_name']),
        ];

        $saved = $this->payment
            ->where('reference', $reference)
            ->update($successPayload);

        if (!$saved) {
            return [
                'status' => 'failed',
                'message' => 'Failed to record payment'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Payment verified successfully',
            'user_id' => (int) $existingPayment['user_id'],
            'plan_id' => (int) $existingPayment['plan_id'],
            'platform' => (string) $existingPayment['platform'],
            'method' => (string) $existingPayment['method'],
            'reference' => $reference,
        ];
    }

    private function redeemVoucher(array $data)
    {
        $existing = $this->voucher
            ->where('code', $data['voucher_code'])
            ->first();

        if (empty($existing)) {
            return [
                'status' => 'failed',
                'message' => 'Invalid voucher code',
            ];
        }

        if ($existing['redeemed']) {
            return [
                'status' => 'failed',
                'message' => 'Voucher code already redeemed',
            ];
        }

        $paymentId = $this->payment->insert([
            'user_id' => $data['user_id'],
            'amount' => 0,
            'plan_id' => $data['plan_id'],
            'reference' => 'voucher-' . $data['voucher_code'],
            'status' => 'success',
            'message' => 'Voucher redeemed',
            'method' => 'voucher',
            'platform' => $data['platform'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);

        $this->voucher
            ->where('code', $data['voucher_code'])
            ->update([
                'redeemed' => 1,
                'redeemed_at' => date('Y-m-d H:i:s'),
                'redeemed_by' => $data['user_id'],
            ]);

        if ($paymentId) {
            return [
                'status' => 'success',
                'message' => 'Voucher redeemed',
                'salt' => bin2hex(random_bytes(16))
            ];
        }

        return [
            'status' => 'failed',
            'message' => 'Failed to redeem voucher'
        ];
    }

    public function hasEntitlement(int $userId, string $platform): bool
    {
        return $this->payment
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->where('platform', $platform)
            ->exists();
    }

    public function getLatestPaidPayment(int $userId, string $platform): ?array
    {
        return $this->payment
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->where('platform', $platform)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function computePrice(int $planId): int
    {
        $row = $this->plan
            ->where('id', $planId)
            ->orderBy('created_at', 'desc')
            ->first();

        $price = (int) $row['price'];
        $discountPercent = $row['discount_percent'] ?? null;

        if ($discountPercent !== null) {
            $discount = ($price * $discountPercent) / 100;
            $price -= (int) $discount;
        }

        return $price * 100; // convert to kobo
    }

    public function checkPaymentStatus(string $reference): array
    {
        $payment = $this->payment
            ->where('reference', $reference)
            ->first();

        if (empty($payment)) {
            return [
                'status' => 'failed',
                'message' => 'Payment not found',
            ];
        }

        return [
            'status' => $payment['status'],
            'message' => 'Payment status retrieved successfully',
        ];
    }

    public function abandonExpiredPendingPayments(): int
    {
        $cutoff = date('Y-m-d H:i:s');

        return $this->payment->rawExecute(
            "
            UPDATE payments
            SET status = :status,
                message = :message
            WHERE status = :pending_status
              AND expires_at IS NOT NULL
              AND expires_at <= :cutoff
            ",
            [
                'status' => 'abandoned',
                'message' => 'Payment abandoned after exceeding the pending window.',
                'pending_status' => 'pending',
                'cutoff' => $cutoff,
            ]
        );
    }

    public function getAbandonedCartReminderCandidates(int $limit = 200): array
    {
        $limit = max(1, $limit);

        return $this->payment->rawQuery(
            sprintf(
                "
                SELECT id
                FROM payments
                WHERE status = 'abandoned'
                  AND method = 'online'
                ORDER BY created_at ASC
                LIMIT %d
                ",
                $limit
            )
        );
    }

    public function getAbandonedCartDetails(int $paymentId): array
    {
        $rows = $this->payment->rawQuery(
            "
            SELECT
                p.id,
                p.user_id,
                p.plan_id,
                p.reference,
                p.amount,
                p.status,
                p.platform,
                p.method,
                p.first_name,
                p.last_name,
                p.expires_at,
                p.created_at,
                u.email,
                pl.name AS plan_name
            FROM payments p
            LEFT JOIN cbt_users u ON u.id = p.user_id
            LEFT JOIN plans pl ON pl.id = p.plan_id
            WHERE p.id = :payment_id
            LIMIT 1
            ",
            [
                'payment_id' => $paymentId,
            ]
        );

        return $rows[0] ?? [];
    }

    private function computePendingPaymentExpiryAt(): string
    {
        return date('Y-m-d H:i:s', strtotime('+' . self::PAYMENT_EXPIRY_MINUTES . ' minutes'));
    }
}
