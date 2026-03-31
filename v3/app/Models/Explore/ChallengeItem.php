<?php

namespace V3\App\Models\Explore;

class ChallengeItem extends \V3\App\Models\BaseModel
{
    protected string $table = 'challenge_items';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
