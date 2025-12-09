<?php

namespace V3\App\Services\Explore;

use V3\APP\Models\Explore\ChallengeParticipants;

class ChallengeParticipantsService
{
    private ChallengeParticipants $challengeParticipants;

    public function __construct(\PDO $pdo)
    {
        $this->challengeParticipants = new ChallengeParticipants($pdo);
    }
}
