<?php

namespace V3\App\Models\Portal\Academics;

use V3\App\Models\BaseModel;

class SkillBehavior extends BaseModel
{
    private string $table = 'skill_table';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}