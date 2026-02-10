<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class ExamType extends BaseModel
{
    protected string $table = "exam_type";

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
