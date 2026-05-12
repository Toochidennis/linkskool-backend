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
        $income = $this->transaction
            ->select(['SUM(amount) as total_income'])
            ->where('trans_type', 'receipt')
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('status', 1)
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
                'amount',
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
            ->where('status', 1)
            ->where('sub', '=', 0)
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->orderBy(['date' => 'DESC', 'term' => 'DESC'])
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
        }

        return [
            'income' => $income['total_income'] ?? 0,
            'outstanding' => $outstanding['total_outstanding'] ?? 0,
            'transactions' => $transactions,
        ];
    }

    public function paidInvoices(array $filters): array
    {
        $stats = $this->transaction
            ->select([
                'SUM(amount) as total_amount',
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
                'amount',
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
            ->get();

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
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 20)));

        $this->transaction
            ->select([
                'tid AS id',
                'trans_type AS type',
                'ref AS reference',
                'cref AS reg_no',
                'memo AS description',
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
                'account AS account_number',
                'account_name',
            ])
            ->where('trans_type', '=', $filters['type'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term']);

        if (!empty($filters['class_id'])) {
            $this->transaction->where('class', '=', $filters['class_id']);
        }

        $result = $this->transaction
            ->orderBy(['date' => 'DESC'])
            ->paginate($page, $perPage);

        $levels = $this->level->select(['id', 'level_name'])->get();
        $levelNames = [];
        foreach ($levels as $level) {
            $levelNames[$level['id']] = $level['level_name'];
        }

        foreach ($result['data'] as &$trans) {
            $trans['level_name'] = $levelNames[$trans['level_id']] ?? 'Unknown Level';
        }

        return $result;
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
            ->where('status', '=', 0)
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

        $grouped = [];
        foreach ($invoices as $invoice) {
            $sid = $invoice['student_id'];

            if (!isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'student_id' => $sid,
                    'reg_no' => $invoice['reg_no'],
                    'name' => ucwords(strtolower($invoice['name'])),
                    'level_id' => $invoice['level_id'],
                    'class_id' => $invoice['class_id'],
                    'invoices' => [],
                ];
            }

            $grouped[$sid]['invoices'][] = [
                'id' => $invoice['id'],
                'reference' => $invoice['reference'],
                'invoice_details' => json_decode($invoice['invoice_details'], true),
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
}
