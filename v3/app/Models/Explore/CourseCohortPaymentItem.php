<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class  CourseCohortPaymentItem extends BaseModel
{
    protected $table = "course_cohort_payment_items";

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
