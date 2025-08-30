<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\Transaction;

class IncomeService
{
    private Transaction $transaction;

    public function __construct(\PDO $pdo)
    {
        $this->transaction = new Transaction($pdo);
    }

    public function getLatestIncome(array $filters): array
    {
        return [];
    }
}
