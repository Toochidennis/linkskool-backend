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

    public function getStudentInvoices(int $studentId): array
    {
        $invoices = $this->transaction
            ->select([
                'tid AS id',
                'description',
                'amount',
                'amount_paid',
                'amount_due',
                'year',
                'term',
                'status',
            ])
            ->where('cid', '=', $studentId)
            ->where('trans_type', '=', 'invoice')
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->get();

        if (empty($invoices)) {
            return [];
        }

        $receipts = $this->transaction
            ->select(['it_id', 'description'])
            ->where('cid', '=', $studentId)
            ->where('trans_type', '=', 'receipt')
            ->get();

        $receiptsByInvoiceId = [];
        foreach ($receipts as $receipt) {
            $receiptsByInvoiceId[(int) $receipt['it_id']][] = $receipt;
        }

        $result = [];

        foreach ($invoices as $invoice) {
            $invoiceId = $invoice['id'];
            $allItems = json_decode($invoice['description'], true) ?? [];

            $paidItems = [];
            foreach ($receiptsByInvoiceId[$invoiceId] ?? [] as $receipt) {
                foreach (json_decode($receipt['description'], true) ?? [] as $item) {
                    $paidItems[$item['fee_id']] = $item;
                }
            }

            $outstandingItems = array_values(
                array_filter($allItems, fn($item) => !isset($paidItems[$item['fee_id']]))
            );

            $result[] = [
                'id' => $invoiceId,
                'invoice_details' => $allItems,
                'items_paid' => array_values($paidItems),
                'outstanding_items' => $outstandingItems,
                'outstanding_total' => array_sum(array_column($outstandingItems, 'amount')),
                'amount' => $invoice['amount'],
                'amount_paid' => $invoice['amount_paid'],
                'amount_due' => $invoice['amount_due'],
                'year' => $invoice['year'],
                'term' => $invoice['term'],
                'status' => $invoice['status'],
            ];
        }

        return $result;
    }

    public function getPaymentHistory(int $studentId, array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filters['limit'] ?? 20)));

        $query = $this->transaction
            ->select([
                'tid AS id',
                'it_id AS invoice_id',
                'ref AS reference',
                'cref AS reg_no',
                'name',
                'amount_paid AS amount',
                'description',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
            ])
            ->where('cid', '=', $studentId)
            ->where('trans_type', '=', 'receipt');

        if (!empty($filters['year'])) {
            $query->where('year', '=', $filters['year']);
        }

        if (!empty($filters['term'])) {
            $query->where('term', '=', $filters['term']);
        }

        $result = $query->orderBy(['year' => 'DESC', 'date' => 'DESC'])->paginate($page, $limit);

        $levels = $this->level->select(['id', 'level_name'])->get();
        $levelNames = [];
        foreach ($levels as $level) {
            $levelNames[$level['id']] = $level['level_name'];
        }

        $result['data'] = array_map(function ($row) use ($levelNames) {
            $row['items_paid'] = json_decode($row['description'], true) ?? [];
            $row['level_name'] = $levelNames[$row['level_id']] ?? null;
            $row['name'] = $this->normalizeName($row['name'] ?? null);
            unset($row['description']);
            return $row;
        }, $result['data']);

        return $result;
    }

    private function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $name = trim($name);

        return $name === '' ? $name : ucwords(strtolower($name));
    }

    public function addPayment(array $data): array
    {
        $items = $data['items'];
        $data['amount'] = (float) array_sum(array_column($items, 'amount'));

        if ($data['type'] === 'online') {
            return $this->initiatePayment($data);
        }

        $reference = 'SFEES-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $this->transaction->insert([
            'trans_type' => 'receipt',
            'memo' => 'School Fees for ' . $data['year'] . ' Term ' . $data['term'],
            'description' => json_encode($items),
            'c_type' => 1,
            'ref' => $reference,
            'cid' => $data['student_id'],
            'cref' => $data['reg_no'],
            'name' => $data['name'],
            'quantity' => 1,
            'it_id' => $data['invoice_id'],
            'amount' => $data['amount'],
            'amount_paid' => $data['amount'],
            'amount_due' => 0,
            'date' => date('Y-m-d'),
            'account' => 1980,
            'account_name' => 'Income',
            'status' => 1,
            'class' => $data['class_id'],
            'level' => $data['level_id'],
            'year' => $data['year'],
            'term' => $data['term'],
        ]);

        $this->updateInvoice((int) $data['invoice_id'], $data['amount']);

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
            'description' => json_encode($data['items']),
            'c_type' => 1,
            'ref' => $reference,
            'cid' => $data['student_id'],
            'cref' => $data['reg_no'],
            'name' => $data['name'],
            'quantity' => 1,
            'it_id' => $data['invoice_id'],
            'amount' => $data['amount'],
            'amount_paid' => 0,
            'amount_due' => $data['amount'],
            'date' => date('Y-m-d'),
            'account' => 1980,
            'account_name' => 'Income',
            'status' => 0,
            'class' => $data['class_id'],
            'level' => $data['level_id'],
            'year' => $data['year'],
            'term' => $data['term'],
        ]);

        $paystack = new PaystackService();

        $payment = $paystack->initialize([
            'email' => $data['email'],
            'amount' => (int) round($data['amount'] * 100),
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
            $reason = $verification['status'] ?? 'unknown';
            return ['status' => 'failed', 'message' => 'Payment not successful: ' . $reason];
        }

        $receipt = $this->transaction
            ->select(['tid', 'it_id', 'amount', 'status'])
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

        $invoiceId = (int) $receipt['it_id'];
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
                ->update(['amount_paid' => $newAmountPaid, 'amount_due' => 0, 'status' => 1]);
        }

        return (bool) $this->transaction
            ->where('tid', '=', $invoiceId)
            ->update(['amount_paid' => $newAmountPaid, 'amount_due' => $newAmountDue]);
    }
}
