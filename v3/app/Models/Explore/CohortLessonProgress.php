<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class CohortLessonProgress extends BaseModel
{
    protected string $table = 'cohort_lesson_progress';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
