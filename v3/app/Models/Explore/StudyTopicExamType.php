<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class StudyTopicExamType extends BaseModel
{
    protected string $table = 'study_topic_exam_types';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
