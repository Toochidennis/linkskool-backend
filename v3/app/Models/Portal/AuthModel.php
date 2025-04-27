<?php

namespace V3\App\Models\Portal;

use PDO;
use V3\App\Models\BaseModel;

class AuthModel extends BaseModel
{
    protected string $table = 'school_data';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
