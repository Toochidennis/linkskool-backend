<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class CohortLessonQuiz extends BaseModel
{
    protected string $table = 'cohort_lesson_quizzes';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
