<?php

namespace V3\App\Models\Portal\Payments;

use V3\App\Models\BaseModel;

class NextTermFee extends BaseModel
{
    private string $table = 'next_term_fees';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
