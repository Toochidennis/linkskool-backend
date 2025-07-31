<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Academics\Level;
use V3\App\Models\Portal\Payments\Transaction;

class StudentPaymentService
{
    private Transaction $transaction;
    private Level $level;

    public function __construct(\PDO $pdo)
    {
        $this->transaction = new Transaction($pdo);
        $this->level = new Level($pdo);
    }

    public function getInvoiceAndTransactionHistory(int $studentId): array
    {
        $formatted = [];
        $levelNames = [];

        $transactions = $this->transaction
            ->select([
                'tid AS id',
                'trans_type AS type',
                'ref AS reference',
                'cref AS reg_no',
                'description',
                'name',
                'amount',
                'date',
                'year',
                'term',
                'level AS level_id',
                'class AS class_id',
            ])
            ->where('cid', '=', $studentId)
            ->where('approved', '=', 1)
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->get();

        $levels = $this->level
            ->select(['id', 'level_name'])
            ->get();

        foreach ($levels as $level) {
            $levelNames[$level['id']] = $level['level_name'];
        }

        foreach ($transactions as $trans) {
            $type = $trans['type'];
            $levelId = $trans['level_id'];
            $levelName = $levelNames[$levelId] ?? 'Unknown Level';

            if ($type === 'invoice') {
                $invoiceDetails = json_decode($trans['description'], true);
                $formatted[$type][] = [
                    'id' => $trans['id'],
                    'invoice_details' => $invoiceDetails,
                    'amount' => $trans['amount'],
                    'year' => $trans['year'],
                    'term' => $trans['term'],
                ];
            } else {
                $formatted['payment'][] = [
                    ...$trans,
                    'level_name' => $levelName,
                ];
            }
        }

        return $formatted;
    }
}
