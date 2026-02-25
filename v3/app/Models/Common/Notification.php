<?php

namespace V3\App\Models\Common;

use V3\App\Models\BaseModel;

class Notification extends BaseModel
{
    protected string $table = 'notifications';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
