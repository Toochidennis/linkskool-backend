<?php

namespace V3\App\Models\Explore;

use PDO;
use V3\App\Models\BaseModel;

class News extends BaseModel
{
    protected $table = 'explore_news';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
