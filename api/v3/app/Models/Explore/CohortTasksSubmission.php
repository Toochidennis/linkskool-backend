<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class CohortTasksSubmission extends BaseModel
{
    protected string $table = 'cohort_tasks_submissions';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
