<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class StudyTopic extends BaseModel
{
    protected $table = 'study_topics';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
