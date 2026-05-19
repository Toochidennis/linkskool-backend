<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class ClassroomCourseCode extends BaseModel
{
    protected string $table = 'classroom_course_codes';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
