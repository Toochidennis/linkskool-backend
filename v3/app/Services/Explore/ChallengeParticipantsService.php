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

    public function storeParticipantData(array $data): int
    {
        // Check if participant already exists for this challenge
        $existingEntry = $this->challengeParticipants
            ->where('user_id', '=', $data['user_id'])
            ->where('challenge_id', '=', $data['challenge_id'])
            ->first();

        return (empty($existingEntry)) ?
            $this->challengeParticipants->insert($data) :
            $existingEntry['id'];
    }
}
