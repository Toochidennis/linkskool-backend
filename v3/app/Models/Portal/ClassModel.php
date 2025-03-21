<?php

namespace V3\App\Models\Portal;

use PDO;
use V3\App\Models\BaseModel;

class ClassModel extends BaseModel
{
    protected string $table = 'class_table';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
