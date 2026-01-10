<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
