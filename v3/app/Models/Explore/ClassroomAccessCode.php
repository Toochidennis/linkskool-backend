<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class ClassroomAccessCode extends BaseModel
{
    protected string $table = 'classroom_access_codes';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
