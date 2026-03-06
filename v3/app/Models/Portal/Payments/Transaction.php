<?php

namespace V3\App\Models\Portal\Payments;

use V3\App\Models\BaseModel;

class Transaction extends BaseModel
{
    private string $table = 'transactions';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
