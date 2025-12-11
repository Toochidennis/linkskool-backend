<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\ChallengeParticipants;
use V3\App\Models\Explore\Leaderboard;

class LeaderboardService
{
    private Leaderboard $leaderboard;
    private ChallengeParticipants $challengeParticipants;

    public function __construct(\PDO $pdo)
    {
        $this->leaderboard = new Leaderboard($pdo);
        $this->challengeParticipants = new ChallengeParticipants($pdo);
    }

    public function storeLeaderboardData(array $data): int
    {
        // Check if user already exists for this challenge
        $existingEntry = $this->leaderboard
            ->where('user_id', '=', $data['user_id'])
            ->where('challenge_id', '=', $data['challenge_id'])
            ->first();

        if (!empty($existingEntry)) {
            // User exists for this competition
            if ($data['score'] > $existingEntry['score']) {
                // New score is higher, update the record
                $this->leaderboard
                    ->where('id', '=', $existingEntry['id'])
                    ->update($data);

                $result = $existingEntry['id'];
            } else {
                // Keep the existing higher score
                $result = $existingEntry['id'];
            }
        } else {
            // New user for this competition, insert new record
            $result = $this->leaderboard->insert($data);

            if ($result) {
                $this->storeParticipantData([
                    'user_id' => $data['user_id'],
                    'username' => $data['username'],
                    'challenge_id' => $data['challenge_id'],
                ]);
            }
        }

        // Recalculate ranks for this challenge
        $this->updateLeaderboardRanks($data['challenge_id']);

        return $result;
    }

    private function updateLeaderboardRanks(int $challengeId): void
    {
        // Fetch all leaderboard entries for this specific challenge ordered by score (highest first)
        $entries = $this->leaderboard
            ->where('challenge_id', '=', $challengeId)
            ->orderBy('score', 'DESC')
            ->get();

        // Assign ranks based on sorted scores
        $currentRank = 1;
        $previousScore = null;
        $actualRank = 1;

        foreach ($entries as $index => $entry) {
            // Handle tied scores - users with same score get the same rank
            if ($previousScore !== null && $entry['score'] < $previousScore) {
                $currentRank = $actualRank;
            }

            // Update the rank for this entry
            $this->leaderboard
                ->where('id', '=', $entry['id'])
                ->update(['rank' => $currentRank, 'updated_at' => date('Y-m-d H:i:s')]);

            $previousScore = $entry['score'];
            $actualRank++;
        }
    }

    private function storeParticipantData(array $data): void
    {
        $this->challengeParticipants->insert($data);
    }

    public function getLeaderboardByChallenge(int $challengeId): array
    {
        return $this->leaderboard
            ->where('challenge_id', '=', $challengeId)
            ->orderBy('rank', 'ASC')
            ->get();
    }
}
