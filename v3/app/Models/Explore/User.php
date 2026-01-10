<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class User extends BaseModel
{
    protected string $table = 'users';

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
