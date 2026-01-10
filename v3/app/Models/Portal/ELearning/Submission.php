<?php

namespace V3\App\Models\Portal\ELearning;

use V3\App\Models\BaseModel;

class Submission extends BaseModel
{
    private string $table = 'responses';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
