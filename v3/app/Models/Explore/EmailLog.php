<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class EmailLog extends BaseModel
{
    protected string $table = 'email_logs';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
