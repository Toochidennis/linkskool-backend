<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\Payments\FeeType;
use V3\App\Models\Portal\Payments\NextTermFee;
use V3\App\Models\Portal\Payments\Transaction;

class NextTermFeeService
{
    private FeeType $feeType;
    private NextTermFee $nextTermFee;
    private Transaction $transaction;
    private Student $student;

    public function __construct(\PDO $pdo)
    {
        $this->feeType = new FeeType($pdo);
        $this->nextTermFee = new NextTermFee($pdo);
        $this->transaction = new Transaction($pdo);
        $this->student = new Student($pdo);
    }

    /**
     * Insert or skip (if exists)
     */
    public function upsertFeeAmount(array $data): bool
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

            $exists = $this->nextTermFee
                ->whereGroup($conditions)
                ->exists();

            if (!$exists) {
                $inserted = $this->nextTermFee->insert($payload);

                if (!$inserted) {
                    return false;
                }
            } else {
                $updated = $this->nextTermFee->whereGroup($conditions)
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
            $tid = $data['year'] . $data['term'] . $student['id'];
            $description = json_encode($data['fees']);
            $amount = array_reduce(
                $data['fees'],
                fn($carry, $fee) => bcadd($carry, $fee['amount'], 2),
                '0.00'
            );

            $existing = $this->transaction
                ->where('tid', '=', $tid)
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
                'amount' => $amount,
                'amount_due' => $amount,
                'date' => date('Y-m-d H:i:s'),
                'account' => 1980,
                'account_name' => 'Income',
                'approved' => 1,
                'sub' => 1,
                'class' => $student['class_id'],
                'level' => $data['level_id'],
                'year' => $data['year'],
                'term' => $data['term'],
            ];

            $success = false;

            if ($existing) {
                $success = $this->transaction
                    ->where('tid', '=', $tid)
                    ->update([
                        'amount' => $amount,
                        'amount_due' => $amount,
                        'description' => $description
                    ]);
            } else {
                $payload['tid'] = $tid;
                $success = $this->transaction->insert($payload);
            }

            if (!$success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fetch fees and structure by fee_id
     */
    public function termFeesByLevel(array $filters): array
    {
        return $this->feeType
            ->select([
                'tid AS fee_id',
                'item.description AS fee_name',
                'IFNULL(item.type, 0) AS is_mandatory',
                'IFNULL(next_term_fees.amount, 0) AS amount'
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
