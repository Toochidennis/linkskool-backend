<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class StudySubTopic extends BaseModel
{
    protected string $table = 'study_sub_topics';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
