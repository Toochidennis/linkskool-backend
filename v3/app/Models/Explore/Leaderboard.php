<?php

namespace V3\App\Models\Explore;

use V3\App\Models\BaseModel;

class Leaderboard extends BaseModel
{
    protected $table = 'leaderboard';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table($this->table);
    }
}
