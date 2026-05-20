<?php

namespace V3\App\Models\Explore\Classroom;

use V3\App\Models\BaseModel;

class ClassroomCourseLessonFile extends BaseModel
{
    protected string $table = 'classroom_course_lesson_files';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
