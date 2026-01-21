<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Program extends BaseModel
{
    protected string $table = 'programs';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
