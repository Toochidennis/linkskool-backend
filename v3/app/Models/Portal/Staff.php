<?php

namespace V3\App\Models\Portal;

use PDO;
use V3\App\Models\BaseModel;

class Staff extends BaseModel
{
    protected string $table = 'staff_record';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
