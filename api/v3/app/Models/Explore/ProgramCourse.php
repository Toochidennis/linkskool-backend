<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class ProgramCourse extends BaseModel
{
    protected string $table = 'program_courses';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
