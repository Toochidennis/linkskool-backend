<?php

namespace V3\App\Models\Portal\Results;

use PDO;
use V3\App\Models\BaseModel;

class StudentSkillBehavior extends BaseModel
{
    private string $table = 'students_skill_table';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
