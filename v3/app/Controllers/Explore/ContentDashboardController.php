<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\ContentDashboardService;

#[Group('/public')]
class ContentDashboardController extends ExploreBaseController
{
    private ContentDashboardService $contentDashboardService;

    public function __construct()
    {
        parent::__construct();
        $this->contentDashboardService = new ContentDashboardService($this->pdo);
    }

    #[Route('/dashboard', 'GET', ['api', 'auth', 'role:admin'])]
    public function getDashboardData()
    {
        $this->respond(
            [
                'success' => true,
                'data' => $this->contentDashboardService->getDashboardData()
            ]
        );
    }

    #[Route('/activity-logs', 'GET', ['api', 'auth', 'role:admin'])]
    public function getActivityLogs(array $vars): void
    {
        $filters = $this->validate($vars, [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $this->respond(
            [
                'success' => true,
                'data' => $this->contentDashboardService->getActivityLogs($filters)
            ]
        );
    }
}
