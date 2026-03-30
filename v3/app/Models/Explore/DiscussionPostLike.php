<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class DiscussionPostLike extends BaseModel
{
    protected string $table = 'discussion_post_likes';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
