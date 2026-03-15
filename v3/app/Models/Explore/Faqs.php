<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Faqs extends BaseModel
{
    protected string $table = 'faqs';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
