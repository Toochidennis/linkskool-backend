<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class CohortEnrollmentProfile extends BaseModel
{
    protected string $table = 'cohort_enrollment_profiles';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
