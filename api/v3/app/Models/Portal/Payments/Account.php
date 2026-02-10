<?php

namespace V3\App\Models\Portal\Payments;

use V3\App\Models\BaseModel;

class Account extends BaseModel
{
    private string $table = 'account_chart';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
