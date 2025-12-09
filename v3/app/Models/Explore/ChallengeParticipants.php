<?php

namespace V3\APP\Models\Explore;

use V3\App\Models\BaseModel;

class ChallengeParticipants extends BaseModel
{
    protected string $table = 'challenge_participants';

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        parent::table($this->table);
    }
}
