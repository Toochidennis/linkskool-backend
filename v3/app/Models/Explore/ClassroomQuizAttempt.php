<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class ClassroomQuizAttempt extends BaseModel
{
    protected string $table = 'classroom_quiz_attempts';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
