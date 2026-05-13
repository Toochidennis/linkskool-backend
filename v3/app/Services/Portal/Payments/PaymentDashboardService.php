<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Academics\Level;
use V3\App\Models\Portal\Payments\Transaction;

class PaymentDashboardService
{
    private Transaction $transaction;
    private Level $level;

    public function __construct(\PDO $pdo)
    {
        $this->transaction = new Transaction($pdo);
        $this->level = new Level($pdo);
    }

    public function getSummary(array $filters): array
    {
        $expected = $this->transaction
            ->select(['SUM(amount) as total_expected'])
            ->where('trans_type', 'invoice')
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->first();

        $income = $this->transaction
            ->select(['SUM(amount_paid) as total_income'])
            ->where('trans_type', 'receipt')
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('status', '=', 1)
            ->first();

        $outstanding = $this->transaction
            ->select(['SUM(amount_due) as total_outstanding'])
            ->where('trans_type', 'invoice')
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('status', 0)
            ->first();

        $transactions = $this->transaction
            ->select([
                'tid AS id',
                'trans_type AS type',
                'ref AS reference',
                'cref AS reg_no',
                'memo AS description',
                'name',
                'amount_paid AS amount',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
                'account AS account_number',
                'account_name',
            ])
            ->where('trans_type', '=', 'receipt')
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('status', '=', 1)
            ->orderBy(['date' => 'DESC', 'term' => 'DESC'])
            ->limit(25)
            ->get();

        $levels = $this->level
            ->select(['id', 'level_name'])
            ->get();

        foreach ($levels as $level) {
            $levelNames[$level['id']] = $level['level_name'];
        }

        foreach ($transactions as &$trans) {
            $levelId = $trans['level_id'];
            $trans['level_name'] = $levelNames[$levelId] ?? 'Unknown Level';
            $trans['name'] = $this->normalizeName($trans['name'] ?? null);
        }

        return [
            'expected' => $expected['total_expected'] ?? 0,
            'income' => $income['total_income'] ?? 0,
            'outstanding' => $outstanding['total_outstanding'] ?? 0,
            'transactions' => $transactions,
        ];
    }

    public function paidInvoices(array $filters): array
    {
        $stats = $this->transaction
            ->select([
                'SUM(amount_paid) as total_amount',
                'COUNT(*) as count',
            ])
            ->where('class', '=', $filters['class_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('trans_type', '=', 'receipt')
            ->where('status', '=', 1)
            ->first();

        $data = $this->transaction
            ->select([
                'tid AS id',
                'ref AS reference',
                'cref AS reg_no',
                'memo as description',
                'name',
                'amount_paid AS amount',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
            ])
            ->where('class', '=', $filters['class_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('trans_type', '=', 'receipt')
            ->where('status', '=', 1)
            ->orderBy(['date' => 'DESC', 'term' => 'DESC'])
            ->get();

        foreach ($data as &$row) {
            $row['name'] = $this->normalizeName($row['name'] ?? null);
        }

        return [
            'stats' => [
                'total_amount' => $stats['total_amount'] ?? 0,
                'count' => (int) ($stats['count'] ?? 0),
            ],
            'data' => $data,
        ];
    }

    public function listTransactions(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filters['limit'] ?? 20)));

        $stats = $this->transaction
            ->select([
                "SUM(CASE WHEN trans_type = 'receipt' THEN amount_paid ELSE 0 END) AS total_income",
                "SUM(CASE WHEN trans_type = 'expenditure' THEN amount_paid ELSE 0 END) AS total_expenditure",
            ])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->in('trans_type', ['receipt', 'expenditure']);

        if (!empty($filters['class_id'])) {
            $stats->where('class', '=', $filters['class_id']);
        }

        $stats = $stats->first();

        $this->transaction
            ->select([
                'tid AS id',
                'trans_type AS type',
                'ref AS reference',
                'cref AS reg_no',
                'memo AS description',
                'name',
                'amount_paid AS amount',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
                'account AS account_number',
                'account_name',
            ])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->in('trans_type', ['receipt', 'expenditure']);

        if (!empty($filters['class_id'])) {
            $this->transaction->where('class', '=', $filters['class_id']);
        }

        $result = $this->transaction
            ->orderBy(['date' => 'DESC'])
            ->paginate($page, $limit);

        $levels = $this->level->select(['id', 'level_name'])->get();
        $levelNames = [];
        foreach ($levels as $level) {
            $levelNames[$level['id']] = $level['level_name'];
        }

        foreach ($result['data'] as &$trans) {
            $trans['level_name'] = $levelNames[$trans['level_id']] ?? '';
            $trans['name'] = $this->normalizeName($trans['name'] ?? null);
        }

        return [
            'stats' => [
                'total_income' => (float) ($stats['total_income'] ?? 0),
                'total_expenditure' => (float) ($stats['total_expenditure'] ?? 0),
            ],
            'data' => [
                'all' => $this->groupTransactionsByMonth($result['data']),
                'receipt' => $this->groupTransactionsByMonth(
                    $this->filterTransactionsByType($result['data'], 'receipt')
                ),
                'expenditure' => $this->groupTransactionsByMonth(
                    $this->filterTransactionsByType($result['data'], 'expenditure')
                ),
            ],
            'meta' => $result['meta'],
        ];
    }

    public function unpaidInvoices(array $filters): array
    {
        $stats = $this->transaction
            ->select([
                'SUM(amount) as total_amount',
                'SUM(amount_paid) as total_amount_paid',
                'SUM(amount_due) as total_amount_due',
                'COUNT(*) as invoice_count',
                'COUNT(DISTINCT cid) as student_count',
            ])
            ->where('class', '=', $filters['class_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('trans_type', '=', 'invoice')
            ->first();

        $invoices = $this->transaction
            ->select([
                'tid AS id',
                'cid AS student_id',
                'cref AS reg_no',
                'ref AS reference',
                'description as invoice_details',
                'name',
                'amount',
                'amount_paid',
                'amount_due',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
            ])
            ->where('class', '=', $filters['class_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('trans_type', '=', 'invoice')
            ->where('status', '=', 0)
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->get();

        $receiptRows = $this->transaction
            ->select(['it_id', 'description'])
            ->where('class', '=', $filters['class_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('trans_type', '=', 'receipt')
            ->where('status', '=', 1)
            ->get();

        $receiptsByInvoiceId = [];
        foreach ($receiptRows as $receipt) {
            $receiptsByInvoiceId[(int) $receipt['it_id']][] = $receipt;
        }

        $grouped = [];
        foreach ($invoices as $invoice) {
            $sid = $invoice['student_id'];

            if (!isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'student_id' => $sid,
                    'reg_no' => $invoice['reg_no'],
                    'name' => $this->normalizeName($invoice['name'] ?? null),
                    'level_id' => $invoice['level_id'],
                    'class_id' => $invoice['class_id'],
                    'invoices' => [],
                ];
            }

            $allItems = json_decode($invoice['invoice_details'], true) ?? [];

            $paidItems = [];
            foreach ($receiptsByInvoiceId[$invoice['id']] ?? [] as $receipt) {
                foreach (json_decode($receipt['description'], true) ?? [] as $item) {
                    $paidItems[$item['fee_id']] = $item;
                }
            }

            $grouped[$sid]['invoices'][] = [
                'id' => $invoice['id'],
                'reference' => $invoice['reference'],
                'invoice_details' => $allItems,
                'items_paid' => array_values($paidItems),
                'amount' => $invoice['amount'],
                'amount_paid' => $invoice['amount_paid'],
                'amount_due' => $invoice['amount_due'],
                'year' => $invoice['year'],
                'term' => $invoice['term'],
            ];
        }

        return [
            'stats' => [
                'total_amount' => $stats['total_amount'] ?? 0,
                'total_amount_paid' => $stats['total_amount_paid'] ?? 0,
                'total_amount_due' => $stats['total_amount_due'] ?? 0,
                'invoice_count' => (int) ($stats['invoice_count'] ?? 0),
                'student_count' => (int) ($stats['student_count'] ?? 0),
            ],
            'data' => array_values($grouped),
        ];
    }

    private function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $name = trim($name);

        return $name === '' ? $name : ucwords(strtolower($name));
    }

    private function filterTransactionsByType(array $transactions, string $type): array
    {
        return array_values(array_filter(
            $transactions,
            fn(array $transaction) => ($transaction['type'] ?? null) === $type
        ));
    }

    private function groupTransactionsByMonth(array $transactions): array
    {
        $groups = [];

        foreach ($transactions as $transaction) {
            $timestamp = strtotime((string) ($transaction['date'] ?? ''));
            $monthKey = $timestamp ? date('Y-m', $timestamp) : 'unknown';

            if (!isset($groups[$monthKey])) {
                $groups[$monthKey] = [
                    'month' => $monthKey,
                    'label' => $timestamp ? date('M Y', $timestamp) : 'Unknown',
                    'total_income' => 0,
                    'total_expenditure' => 0,
                    'total_transactions' => 0,
                    'transactions' => [],
                ];
            }

            $amount = (float) ($transaction['amount_paid'] ?? 0);
            if (($transaction['type'] ?? null) === 'receipt') {
                $groups[$monthKey]['total_income'] += $amount;
            }

            if (($transaction['type'] ?? null) === 'expenditure') {
                $groups[$monthKey]['total_expenditure'] += $amount;
            }

            $groups[$monthKey]['total_transactions']++;
            $groups[$monthKey]['transactions'][] = $transaction;
        }

        return array_values($groups);
    }

    public function getReceiptDetail(int $id): array
    {
        $receipt = $this->transaction
            ->select([
                'tid AS id',
                'it_id',
                'ref AS reference',
                'cref AS reg_no',
                'cid AS student_id',
                'name',
                'amount',
                'description',
                'memo',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
                'status',
            ])
            ->where('tid', '=', $id)
            ->where('trans_type', '=', 'receipt')
            ->first();

        if (empty($receipt)) {
            return [];
        }

        $invoiceId = (int) $receipt['it_id'];
        $receiptOut = $receipt;
        $receiptOut['name'] = $this->normalizeName($receiptOut['name'] ?? null);
        $receiptOut['items_paid'] = json_decode($receipt['description'], true) ?? [];
        unset($receiptOut['description'], $receiptOut['it_id']);

        $invoice = $this->transaction
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
            ->where('tid', '=', $invoiceId)
            ->where('trans_type', '=', 'invoice')
            ->first();

        if (empty($invoice)) {
            return [
                'receipt' => $receiptOut,
                'invoice' => null,
                'payment_history' => [],
            ];
        }

        $allItems = json_decode($invoice['description'], true) ?? [];

        $allReceipts = $this->transaction
            ->select(['tid AS id', 'ref AS reference', 'amount', 'description', 'date', 'status'])
            ->where('it_id', '=', $invoiceId)
            ->where('trans_type', '=', 'receipt')
            ->where('status', '=', 1)
            ->orderBy(['date' => 'ASC'])
            ->get();

        $paidItems = [];
        $paymentHistory = [];

        foreach ($allReceipts as $r) {
            $items = json_decode($r['description'], true) ?? [];
            foreach ($items as $item) {
                $paidItems[$item['fee_id']] = $item;
            }
            $paymentHistory[] = [
                'id' => $r['id'],
                'reference' => $r['reference'],
                'amount' => $r['amount'],
                'items_paid' => $items,
                'date' => $r['date'],
                'status' => $r['status'],
            ];
        }

        $paidItemsList = array_values($paidItems);
        $outstandingItems = array_values(
            array_filter($allItems, fn($item) => !isset($paidItems[$item['fee_id']]))
        );

        return [
            'receipt' => $receiptOut,
            'invoice' => [
                'id' => $invoice['id'],
                'invoice_details' => $allItems,
                'amount' => $invoice['amount'],
                'amount_paid' => $invoice['amount_paid'],
                'amount_due' => $invoice['amount_due'],
                'year' => $invoice['year'],
                'term' => $invoice['term'],
                'status' => $invoice['status'],
            ],
            'paid_summary' => [
                'items' => $paidItemsList,
                'total' => array_sum(array_column($paidItemsList, 'amount')),
            ],
            'outstanding_summary' => [
                'items' => $outstandingItems,
                'total' => array_sum(array_column($outstandingItems, 'amount')),
            ],
            'payment_history' => $paymentHistory,
        ];
    }
}
