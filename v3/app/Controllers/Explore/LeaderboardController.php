<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\LeaderboardService;

#[Group('/public/cbt/challenges/leaderboard')]
class LeaderboardController extends ExploreBaseController
{
    private LeaderboardService $leaderboardService;

    public function __construct()
    {
        parent::__construct();
        $this->leaderboardService = new LeaderboardService($this->pdo);
    }

    #[Route('', 'POST', ['api'])]
    public function storeLeaderboardData(): void
    {
        $validatedData = $this->validate(
            $this->getRequestData(),
            [
                'username' => 'required|string',
                'user_id' => 'required|integer',
                'score' => 'required|integer',
                'challenge_id' => 'required|integer',
                'correct_answers' => 'nullable|integer',
                'total_questions' => 'nullable|integer',
                'time_taken' => 'required|integer',
                'country' => 'nullable|string',
                'state' => 'nullable|string',
                'device' => 'nullable|string',
                'platform' => 'nullable|string',
                'extra_data' => 'nullable|string',
            ]
        );

        $result = $this->leaderboardService->storeLeaderboardData($validatedData);

        if (!$result) {
            $this->respondError(
                'Failed to store leaderboard data',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Leaderboard data stored successfully',
                'data' => ['id' => $result],
            ]
        );
    }

    #[Route('/{challenge_id:\d+}', 'GET', ['api'])]
    public function getLeaderboardByChallenge(array $vars): void
    {
        $validatedData = $this->validate(
            $vars,
            [
                'challenge_id' => 'required|integer',
            ]
        );

        $leaderboardData = $this->leaderboardService->getLeaderboardByChallenge($validatedData['challenge_id']);

        if (empty($leaderboardData)) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'No leaderboard data found for this challenge',
                    'data' => [],
                ],
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Leaderboard data retrieved successfully',
                'data' => $leaderboardData,
            ]
        );
    }
}
