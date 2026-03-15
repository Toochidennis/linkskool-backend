<?php

namespace V3\App\Models\Explore;

use PDO;
use V3\App\Models\BaseModel;

class CourseCohortPayment extends BaseModel
{
    protected $table = "course_cohort_payments";

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
