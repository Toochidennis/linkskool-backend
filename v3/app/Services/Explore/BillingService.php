<?php

namespace V3\App\Services\Explore;

use RuntimeException;
use V3\App\Models\Explore\Payment;
use V3\App\Models\Explore\Voucher;
use V3\App\Services\Paystack\PaystackService;
use V3\App\Models\Explore\Plan;

class BillingService
{
    private Payment $payment;
    private Voucher $voucher;
    private Plan $plan;

    public function __construct(\PDO $pdo)
    {
        $this->payment = new Payment($pdo);
        $this->voucher = new Voucher($pdo);
        $this->plan = new Plan($pdo);
    }

    public function verifyPaymentOnline(array $data)
    {
        $paystack = new PaystackService();
        $verification = $paystack->verify($data['reference']);

        if (!$verification['status']) {
            return [
                'status' => 'failed',
                'message' => 'Payment verification failed: ' . $verification['message'],
            ];
        }

        $expected = $this->computePrice($data['platform']) * 100; // convert to kobo
        $amountPaid =  $verification['amount'] * 100; // convert to kobo

        if ($amountPaid !== $expected) {
            return [
                'status' => 'failed',
                'message' => 'Payment amount mismatch: expected ' . $expected . ', got ' . $amountPaid,
            ];
        }

        if ($this->payment->where('reference', $data['reference'])->exists()) {
            return [
                'status' => 'paid',
                'message' => 'Payment already recorded'
            ];
        }

        $paymentId = $this->payment->insert([
            'user_id' => $data['user_id'],
            'amount' => $amountPaid,
            'reference' => $data['reference'],
            'status' => 'paid',
            'message' => 'Payment verified successfully',
            'method' => $data['method'],
            'platform' => $data['platform'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);

        if ($paymentId) {
            return [
                'status' => 'paid',
                'message' => 'Payment verified successfully',
            ];
        }

        return [
            'status' => 'failed',
            'message' => 'Failed to record payment'
        ];
    }

    public function redeemVoucher(array $data)
    {
        $existing = $this->voucher
            ->where('code', $data['voucher_code'])
            ->first();

        if (empty($existing)) {
            throw new RuntimeException('Invalid voucher code');
        }

        if ($existing['redeemed']) {
            throw new RuntimeException('Voucher already used');
        }

        $paymentId = $this->payment->insert([
            'user_id' => $data['user_id'],
            'amount' => 0,
            'reference' => 'voucher-' . $data['voucher_code'],
            'status' => 'paid',
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
                'status' => 'paid',
                'message' => 'Voucher redeemed',
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
            ->where('status', 'paid')
            ->where('platform', $platform)
            ->exists();
    }

    public function getLatestPaidPayment(int $userId, string $platform): ?array
    {
        return $this->payment
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->where('platform', $platform)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function computePrice(string $platform): int
    {
        $row = $this->plan
            ->where('platform', $platform)
            ->orderBy('created_at', 'desc')
            ->first();

        $amount = (int) $row['amount'];
        $discountPercent = $row['discount_percent'] ?? null;

        if ($discountPercent !== null) {
            $discount = ($amount * $discountPercent) / 100;
            $amount -= (int) $discount;
        }

        return $amount;
    }
}
