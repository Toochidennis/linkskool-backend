<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Syllabi extends BaseModel
{
    protected string $table = 'syllabi';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
