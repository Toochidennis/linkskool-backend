<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\Account;

class AccountService
{
    private Account $account;

    public function __construct(\PDO $pdo)
    {
        $this->account = new Account($pdo);
    }

    public function addAccount(array $data)
    {
        $payload  = [
            'account_name' => $data['account_name'],
            'account_type' => $data['account_type'],
            'account_id' => $data['account_number'],
        ];

        if ($this->isDuplicate($payload)) {
            return false;
        } else {
            return $this->account->insert([...$payload, 'inactive' => 'FALSE']);
        }
    }

    public function updateAccount(array $data): bool|int
    {
        $payload  = [
            'account_name' => $data['account_name'],
            'account_type' => $data['account_type'],
            'account_id' => $data['account_number'],
        ];

        if ($this->isDuplicate($payload)) {
            return false;
        } else {
            return $this->account
                ->where('typeid', '=', $data['id'])
                ->update($payload);
        }
    }

    private function isDuplicate(array $payload): bool
    {
        return $this->account
            ->where(function ($q) use ($payload) {
                $q->where('account_id', '=', $payload['account_id'])
                    ->orWhere('account_name', '=', $payload['account_name']);
            })
            ->exists();
    }

    public function getPaginatedAccounts(int $page, int $limit)
    {
        return $this->account
            ->select(['typeid AS id', 'account_name', 'account_type', 'account_id AS account_number', 'inactive'])
            ->where('inactive', '=', 'FALSE')
            ->paginate($page, $limit);
    }

    public function deleteAccount(int $id): bool|int
    {
        return $this->account->where('typeid', '=', $id)->delete();
    }
}
