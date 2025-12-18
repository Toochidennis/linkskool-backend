<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Category extends BaseModel
{
    protected string $table = "categoryTable";

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
