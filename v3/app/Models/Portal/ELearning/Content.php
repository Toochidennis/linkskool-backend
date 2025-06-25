<?php

namespace V3\App\Models\Portal\ELearning;

use V3\App\Models\BaseModel;

class Content extends BaseModel
{
    protected string $table = 'link';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
