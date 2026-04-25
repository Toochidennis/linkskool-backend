<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CbtGamifyService;

#[Group("/public")]
class CbtGamifyController extends ExploreBaseController
{
    private CbtGamifyService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CbtGamifyService($this->pdo);
    }

    #[Route("/cbt/gamify/leaderboard", "POST", ['api'])]
    public function storeLeaderboardData()
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'user_id' => 'required|integer',
                'username' => 'required|string',
                'exam_type_id' => 'required|integer',
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'score' => 'required|integer',
            ]
        );

        $res = $this->service->storeLeaderboardData($data);

        if (!$res) {
            $this->respondError(
                "Failed to store leaderboard data",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => "Leaderboard data stored successfully",
                'data' => $data
            ]
        );
    }

    #[Route("/cbt/gamify/leaderboard/summary", "GET", ['api'])]
    public function getLeaderboardSummary(array $vars): void
    {
        $data = $this->validate(
            $vars,
            [
                'user_id' => 'required|integer|min:1',
                'exam_type_id' => 'required|integer|min:1',
            ]
        );

        $summary = $this->service->getLeaderboardSummary(
            (int) $data['user_id'],
            (int) $data['exam_type_id']
        );

        $this->respond([
            'success' => true,
            'data' => $summary,
        ]);
    }

    #[Route("/cbt/gamify/leaderboard", "GET", ['api'])]
    public function getFullLeaderboard(array $vars): void
    {
        $data = $this->validate(
            $vars,
            [
                'exam_type_id' => 'required|integer|min:1',
                'course_id' => 'nullable|integer|min:1',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:100',
            ]
        );

        $leaderboard = $this->service->getFullLeaderboard(
            (int) $data['exam_type_id'],
            isset($data['course_id']) ? (int) $data['course_id'] : null,
            (int) ($data['page'] ?? 1),
            (int) ($data['limit'] ?? 25)
        );

        $this->respond([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }
}
