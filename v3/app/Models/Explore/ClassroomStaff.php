<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class ClassroomStaff extends BaseModel
{
    protected string $table = 'classroom_staff';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
