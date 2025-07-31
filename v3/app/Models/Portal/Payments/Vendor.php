<?php

namespace V3\App\Models\Portal\Payments;

use V3\App\Models\BaseModel;

class Vendor extends BaseModel
{
    private string $table = 'customer';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
