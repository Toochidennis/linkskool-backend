<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class LessonAttendance extends BaseModel
{
    protected string $table = 'cohort_lesson_attendance';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
