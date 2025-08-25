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
            ->where('status', 1)
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
            'income'      => $income['total_income'] ?? 0,
            'invoiced'    => $invoiced['total_invoiced'] ?? 0,
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
            ->where('level', '=', $filters['level_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('status', '=', 1)
            ->where('trans_type', '=', 'receipts')
            ->get();
    }
    public function unpaidInvoices(array $filters): array
    {
        return $this->transaction
            ->select([
                'tid AS id',
                'ref AS reference',
                'cref AS reg_no',
                'description as invoice_details',
                'name',
                'amount',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
            ])
            ->where('level', '=', $filters['level_id'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('approved', '=', 1)
            ->where('trans_type', '=', 'invoice')
            ->get();
    }
}
