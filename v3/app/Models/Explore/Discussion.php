<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Discussion extends BaseModel
{
    protected string $table = 'discussions';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
