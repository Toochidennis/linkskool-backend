<?php

namespace V3\App\Models\Explore;

class CourseCohortTransaction extends BaseModel
{
    protected string $table = 'course_cohort_transactions';

    public function __construct(\PDO $pdo)
    {
        parent::table($pdo);
        parent::table($this->table);
    }
}