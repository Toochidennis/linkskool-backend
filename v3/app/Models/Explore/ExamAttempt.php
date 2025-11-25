<?php

namespace V3\App\Models\Explore;

class ExamAttempt extends \V3\App\Models\BaseModel
{
    protected string $table = 'exam_attempts';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
