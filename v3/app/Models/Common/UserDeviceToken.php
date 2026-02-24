<?php

namespace V3\App\Models\Common;

use V3\App\Models\BaseModel;

class UserDeviceToken extends BaseModel
{
    protected string $table = 'user_device_tokens';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
