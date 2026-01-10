<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Syllabus extends BaseModel
{
    protected string $table = 'syllabi';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
