<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class DiscussionPost extends BaseModel
{
    protected string $table = 'discussion_posts';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
