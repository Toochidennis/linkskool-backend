<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class ProgramCohortCourseEnrollment extends BaseModel
{
    protected string $table = 'program_cohort_course_enrollments';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
