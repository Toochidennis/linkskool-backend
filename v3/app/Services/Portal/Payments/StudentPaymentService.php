<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Academics\Level;
use V3\App\Models\Portal\Payments\Transaction;
use V3\App\Services\Paystack\PaystackService;

class StudentPaymentService
{
    private Transaction $transaction;
    private Level $level;
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->transaction = new Transaction($pdo);
        $this->level = new Level($pdo);
    }

    public function getInvoiceAndTransactionHistory(int $studentId): array
    {
        $formatted = [
            'invoice' => [],
            'payments' => [],
        ];

        $transactions = $this->transaction
            ->select([
                'tid AS id',
                'trans_type AS type',
                'ref AS reference',
                'cref AS reg_no',
                'cid AS student_id',
                'description',
                'name',
                'amount',
                'amount_paid',
                'amount_due',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
            ])
            ->where('cid', '=', $studentId)
            ->where('approved', '=', 1)
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->get();

        $levels = $this->level->select(['id', 'level_name'])->get();
        $levelNames = [];
        foreach ($levels as $level) {
            $levelNames[$level['id']] = $level['level_name'];
        }

        foreach ($transactions as $trans) {
            $type = $trans['type'];
            $levelName = $levelNames[$trans['level_id']] ?? 'Unknown Level';

            if ($type === 'invoice') {
                $formatted['invoice'][] = [
                    'id' => $trans['id'],
                    'invoice_details' => json_decode($trans['description'], true),
                    'amount' => $trans['amount'],
                    'amount_paid' => $trans['amount_paid'],
                    'amount_due' => $trans['amount_due'],
                    'year' => $trans['year'],
                    'term' => $trans['term'],
                ];
            } else {
                $formatted['payments'][] = [
                    'id' => $trans['id'],
                    'reference' => $trans['reference'],
                    'reg_no' => $trans['reg_no'],
                    'name' => $trans['name'],
                    'amount' => $trans['amount'],
                    'date' => $trans['date'],
                    'year' => $trans['year'],
                    'term' => $trans['term'],
                    'level_id' => $trans['level_id'],
                    'class_id' => $trans['class_id'],
                    'status' => $trans['status'],
                    'level_name' => $levelName,
                ];
            }
        }

        return $formatted;
    }

    public function addPayment(array $data): array
    {
        if ($data['type'] === 'online') {
            return $this->initiatePayment($data);
        }

        $reference = 'SFEES-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $this->transaction->insert([
            'trans_type' => 'receipt',
            'memo' => 'School Fees for ' . $data['year'] . ' Term ' . $data['term'],
            'c_type' => 1,
            'ref' => $reference,
            'cid' => $data['student_id'],
            'cref' => $data['reg_no'],
            'name' => $data['name'],
            'quantity' => 1,
            'it_id' => 1,
            'amount' => $data['amount'],
            'amount_paid' => $data['amount'],
            'amount_due' => 0,
            'date' => date('Y-m-d'),
            'account' => 1980,
            'account_name' => 'Income',
            'sub' => 0,
            'approved' => 1,
            'status' => 1,
            'class' => $data['class_id'],
            'level' => $data['level_id'],
            'year' => $data['year'],
            'term' => $data['term'],
        ]);

        $this->updateInvoice((int) $data['invoice_id'], (float) $data['amount']);

        return [
            'payment_type' => 'offline',
            'success' => true,
            'reference' => $reference,
        ];
    }

    private function initiatePayment(array $data): array
    {
        $reference = 'SFEES-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $this->transaction->insert([
            'trans_type' => 'receipt',
            'memo' => 'School Fees for ' . $data['year'] . ' Term ' . $data['term'],
            'c_type' => 1,
            'ref' => $reference,
            'cid' => $data['student_id'],
            'cref' => $data['reg_no'],
            'name' => $data['name'],
            'quantity' => 1,
            'it_id' => 1,
            'amount' => $data['amount'],
            'amount_paid' => 0,
            'amount_due' => $data['amount'],
            'date' => date('Y-m-d'),
            'account' => 1980,
            'account_name' => 'Income',
            'sub' => 0,
            'approved' => 0,
            'status' => 0,
            'class' => $data['class_id'],
            'level' => $data['level_id'],
            'year' => $data['year'],
            'term' => $data['term'],
        ]);

        $paystack = new PaystackService();

        $payment = $paystack->initialize([
            'email' => $data['email'],
            'amount' => (int) round((float) $data['amount'] * 100), // kobo
            'reference' => $reference,
            'metadata' => [
                'payment_type' => 'school_fees',
                'invoice_id' => $data['invoice_id'],
                'student_id' => $data['student_id'],
            ],
        ]);

        return [
            'payment_type' => 'online',
            'payment_url' => $payment['authorization_url'],
            'reference' => $reference,
        ];
    }

    public function handleWebhookVerification(string $reference): array
    {
        $paystack = new PaystackService();

        try {
            $verification = $paystack->verify($reference);
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }

        if (!$verification['success'] || $verification['status'] !== 'success') {
            return ['status' => 'failed', 'message' => 'Payment not successful: ' . ($verification['status'] ?? 'unknown')];
        }

        $receipt = $this->transaction
            ->select(['tid', 'amount', 'status'])
            ->where('ref', '=', $reference)
            ->where('trans_type', '=', 'receipt')
            ->first();

        if (empty($receipt)) {
            return ['status' => 'failed', 'message' => 'Receipt not found for reference.'];
        }

        if ((int) $receipt['status'] === 1) {
            return ['status' => 'success', 'message' => 'Payment already verified.'];
        }

        $expectedKobo = (int) round((float) $receipt['amount'] * 100);
        if ((int) $verification['amount_kobo'] !== $expectedKobo) {
            return ['status' => 'failed', 'message' => 'Payment amount mismatch.'];
        }

        $metadata = $verification['raw']['data']['metadata'] ?? [];
        $invoiceId = (int) ($metadata['invoice_id'] ?? 0);
        $amount = (float) $receipt['amount'];

        $this->pdo->beginTransaction();

        try {
            $this->transaction
                ->where('ref', '=', $reference)
                ->where('trans_type', '=', 'receipt')
                ->update([
                    'status' => 1,
                    'approved' => 1,
                    'amount_paid' => $amount,
                    'amount_due' => 0,
                ]);

            if ($invoiceId > 0) {
                $this->updateInvoice($invoiceId, $amount);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }

        return ['status' => 'success', 'message' => 'Payment verified and recorded.'];
    }

    public function checkPaymentStatus(string $reference): array
    {
        $receipt = $this->transaction
            ->select(['tid', 'status', 'amount', 'amount_paid', 'amount_due', 'date'])
            ->where('ref', '=', $reference)
            ->where('trans_type', '=', 'receipt')
            ->first();

        if (empty($receipt)) {
            return [
                'exists' => false,
                'is_paid' => false,
                'status' => null,
                'message' => 'Payment reference not found.',
            ];
        }

        $isPaid = (int) $receipt['status'] === 1;

        return [
            'exists' => true,
            'is_paid' => $isPaid,
            'status' => $isPaid ? 'success' : 'pending',
            'message' => $isPaid ? 'Payment confirmed.' : 'Payment is still pending.',
        ];
    }

    private function updateInvoice(int $invoiceId, float $payment): bool
    {
        $invoice = $this->transaction
            ->select(['amount', 'amount_paid', 'amount_due'])
            ->where('tid', '=', $invoiceId)
            ->where('trans_type', '=', 'invoice')
            ->first();

        if (empty($invoice) || (float) $invoice['amount_due'] <= 0 || $payment <= 0) {
            return false;
        }

        $amountDue = (float) $invoice['amount_due'];
        $amountPaid = (float) $invoice['amount_paid'];
        $payment = min($payment, $amountDue);

        $newAmountPaid = $amountPaid + $payment;
        $newAmountDue = $amountDue - $payment;

        if ($newAmountDue <= 0) {
            return (bool) $this->transaction
                ->where('tid', '=', $invoiceId)
                ->update(['amount_paid' => $newAmountPaid, 'amount_due' => 0, 'approved' => 0, 'sub' => 0]);
        }

        return (bool) $this->transaction
            ->where('tid', '=', $invoiceId)
            ->update(['amount_paid' => $newAmountPaid, 'amount_due' => $newAmountDue]);
    }
}
