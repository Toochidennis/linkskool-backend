<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class NewsCategoryPivot extends BaseModel
{
    protected string $table = 'explore_news_category_pivot';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
