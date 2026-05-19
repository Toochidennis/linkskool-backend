<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class ClassroomCourseQuiz extends BaseModel
{
    protected string $table = 'classroom_course_quizzes';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
