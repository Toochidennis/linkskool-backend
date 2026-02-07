<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Payment extends BaseModel
{
    protected string $table = 'payments';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
