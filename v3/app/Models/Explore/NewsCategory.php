<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class NewsCategory extends BaseModel
{
    protected $table = 'explore_news_category';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
