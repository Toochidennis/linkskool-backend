<?php

namespace V3\App\Models\Explore\Classroom;

use V3\App\Models\BaseModel;

class ClassroomCourseQuizSetting extends BaseModel
{
    protected string $table = 'classroom_course_quiz_settings';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
