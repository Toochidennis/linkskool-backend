<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\FeeType;
use V3\App\Models\Portal\Payments\NextTermFee;

class NextTermFeeService
{
    private FeeType $feeType;
    private NextTermFee $nextTermFee;

    public function __construct(\PDO $pdo)
    {
        $this->feeType = new FeeType($pdo);
        $this->nextTermFee = new NextTermFee($pdo);
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
