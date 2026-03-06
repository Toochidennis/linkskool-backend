<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class CourseCohortTransaction extends BaseModel
{
    protected string $table = 'course_cohort_transactions';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
