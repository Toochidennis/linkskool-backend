<?php

namespace V3\App\Models\Portal\Payments;

use V3\App\Models\BaseModel;

class FeeType extends BaseModel
{
    private string $table = 'item';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
