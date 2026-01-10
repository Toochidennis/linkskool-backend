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
            ->where('trans_type', 'receipts')
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('status', 1)
            ->first();

        $invoiced = $this->transaction
            ->select(['SUM(amount) as total_invoiced'])
            ->where('trans_type', 'invoice')
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('approved', 1)
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
            ->where('approved', 1)
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
            'invoiced' => $invoiced['total_invoiced'] ?? 0,
            'transactions' => $transactions,
        ];
    }

    public function paidInvoices(array $filters): array
    {
        return $this->transaction
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
            ->where('trans_type', '=', 'receipts')
            ->get();
    }
    public function unpaidInvoices(array $filters): array
    {
        $invoices =  $this->transaction
            ->select([
                'tid AS id',
                'cid AS student_id',
                'cref AS reg_no',
                'ref AS reference',
                'description as invoice_details',
                'name',
                'amount_due AS amount',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
            ])
            ->where('class', '=', $filters['class_id'])
            ->where('approved', '=', 1)
            ->where('trans_type', '=', 'invoice')
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->get();

        $grouped = [];
        foreach ($invoices as $invoice) {
            $sid = $invoice['student_id'];

            if (!isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'student_id' => $sid,
                    'reg_no' => $invoice['reg_no'],
                    'name' => $invoice['name'],
                    'level_id' => $invoice['level_id'],
                    'class_id' => $invoice['class_id'],
                    'invoices' => [],
                ];
            }

            $grouped[$sid]['invoices'][] = [
                'id' => $invoice['id'],
                'reference' => $invoice['reference'],
                'invoice_details' => json_decode($invoice['invoice_details'], true),
                'amount_due' => $invoice['amount'],
                'year' => $invoice['year'],
                'term' => $invoice['term'],
            ];
        }

        return array_values($grouped);
    }
}
