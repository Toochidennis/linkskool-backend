<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class StudyCategory extends BaseModel
{
    protected string $table = 'study_categories';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
