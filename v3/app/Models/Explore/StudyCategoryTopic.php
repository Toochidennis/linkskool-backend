<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class StudyCategoryTopic extends BaseModel
{
    protected string $table = 'study_category_topics';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
