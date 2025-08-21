<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\Transaction;

class ExpenditureService
{
    private Transaction $transaction;

    public function __construct(\PDO $pdo)
    {
        $this->transaction = new Transaction($pdo);
    }

    public function addExpenditure(array $data)
    {
        $description = [
            'amount' => $data['amount'],
            'desc' => $data['description']
        ];

        $payload = [
            'trans_type' => 'expenditure',
            'c_type' => 2,
            'memo' => $data['description'],
            'cid' => $data['customer_id'],
            'cref' => $data['customer_reference'],
            'name' => $data['customer_name'],
            'description' => json_encode($description),
            'quantity' => 1,
            'it_id' => 1,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'account' => $data['account_number'],
            'account_name' => $data['account_name'],
            'approved' => 1,
            'sub' => 0,
            'status' => 1,
            'year' => $data['year'],
            'term' => $data['term'],
        ];

        return $this->transaction->insert($payload);
    }

    public function updateExpenditure(array $data): bool
    {
        $description = [
            'amount' => $data['amount'],
            'desc' => $data['description']
        ];

        $payload = [
            'trans_type' => 'expenditure',
            'memo' => $data['description'],
            'cid' => $data['customer_id'],
            'cref' => $data['customer_reference'],
            'name' => $data['customer_name'],
            'description' => json_encode($description),
            'amount' => $data['amount'],
            'account' => $data['account_number'],
            'account_name' => $data['account_name'],
            'year' => $data['year'],
            'term' => $data['term'],
        ];

        $existing = $this->transaction
            ->where('tid', '=', $data['id'])
            ->first();

        if ($existing) {
            return $this->transaction
                ->where('tid', '=', $data['id'])
                ->update($payload);
        }

        return false;
    }
}
