<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\Payments\FeeType;
use V3\App\Models\Portal\Payments\Invoice;
use V3\App\Models\Portal\Payments\Transaction;

class InvoiceService
{
    private FeeType $feeType;
    private Invoice $invoice;
    private Transaction $transaction;
    private Student $student;

    public function __construct(\PDO $pdo)
    {
        $this->feeType = new FeeType($pdo);
        $this->invoice = new Invoice($pdo);
        $this->transaction = new Transaction($pdo);
        $this->student = new Student($pdo);
    }

    /**
     * Insert or skip (if exists)
     */
    public function upsertInvoice(array $data): bool
    {
        foreach ($data['fees'] as $fee) {
            $payload = [
                'fee' => $fee['fee_id'],
                'fee_name' => $fee['fee_name'],
                'amount' => $fee['amount'],
                'term' => $data['term'],
                'year' => $data['year'],
                'level' => $data['level_id']
            ];

            $conditions = [
                ['fee', '=', $fee['fee_id']],
                ['term', '=', $data['term']],
                ['year', '=', $data['year']],
                ['level', '=', $data['level_id']]
            ];

            $exists = $this->invoice
                ->whereGroup($conditions)
                ->exists();

            if (!$exists) {
                $inserted = $this->invoice->insert($payload);

                if (!$inserted) {
                    return false;
                }
            } else {
                $updated = $this->invoice->whereGroup($conditions)
                    ->update(['amount' => $fee['amount']]);

                if ($updated === false) {
                    return false;
                }
            }
        }

        return $this->upsertTransaction($data);
    }

    private function upsertTransaction(array $data): bool
    {
        $students = $this->student
            ->select([
                'id',
                "CONCAT(surname, ', ', first_name, ' ', middle) as name",
                'registration_no AS reg_no',
                'student_class AS class_id',
            ])
            ->where('student_level', '=', $data['level_id'])
            ->get();

        if (empty($students)) {
            return false;
        }

        foreach ($students as $student) {
            $filteredFees = array_values(array_filter($data['fees'], fn($fee) => $fee['amount'] > 0));
            $description = json_encode($filteredFees);
            $amount = array_reduce(
                $filteredFees,
                fn($carry, $fee) => bcadd($carry, $fee['amount'], 2),
                '0.00'
            );

            $existing = $this->transaction
                ->select(['tid'])
                ->whereGroup([
                    ['cid', '=', $student['id']],
                    ['term', '=', $data['term']],
                    ['year', '=', $data['year']],
                    ['trans_type', '=', 'invoice']
                ])
                ->first();

            $payload = [
                'trans_type' => 'invoice',
                'c_type' => 1,
                'cid' => $student['id'],
                'cref' => $student['reg_no'],
                'name' => $student['name'],
                'description' => $description,
                'quantity' => 1,
                'it_id' => 1,
                'amount_due' => $amount,
                'date' => date('Y-m-d'),
                'account' => 1980,
                'account_name' => 'Income',
                'approved' => 1,
                'sub' => 1,
                'status' => 0,
                'class' => $student['class_id'],
                'level' => $data['level_id'],
                'year' => $data['year'],
                'term' => $data['term'],
            ];

            $success = false;

            $success = $existing ? $this->transaction
                ->where('tid', '=', $existing['tid'])
                ->update([
                    'amount_due' => $amount,
                    'description' => $description
                ]) : $this->transaction->insert($payload);

            if (!$success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fetch fees and structure by fee_id
     */
    public function getInvoicesByLevel(array $filters): array
    {
        return $this->feeType
            ->select([
                'tid AS fee_id',
                'item.description AS fee_name',
                'IFNULL(item.type, 0) AS is_mandatory',
                'IFNULL(next_term_fees.amount, 0) AS amount',
            ])
            ->join('next_term_fees', function ($join) use ($filters) {
                $join->on('item.tid', '=', 'next_term_fees.fee')
                    ->on('next_term_fees.year', '=', $filters['year'])
                    ->on('next_term_fees.term', '=', $filters['term'])
                    ->on('next_term_fees.level', '=', $filters['level_id']);
            }, 'LEFT')
            ->orderBy('fee_name')
            ->get();
    }
}
