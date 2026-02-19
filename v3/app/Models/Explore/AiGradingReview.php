<?php

namespace V3\App\Models\Explore;

use PDO;
use V3\App\Models\BaseModel;

class AiGradingReview extends BaseModel
{
    protected string $table = 'ai_grading_reviews';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
