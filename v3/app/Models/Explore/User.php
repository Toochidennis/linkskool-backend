<?php

namespace V3\App\Models\Explore;

use PDO;
use V3\App\Models\BaseModel;

class User extends BaseModel
{
    protected string $table = 'cbt_users';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
