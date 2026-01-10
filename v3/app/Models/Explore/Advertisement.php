<?php

namespace V3\App\Models\Explore;

use PDO;
use V3\App\Models\BaseModel;

class Advertisement extends BaseModel
{
    protected string $table = 'advertisements';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
