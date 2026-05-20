<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomDashboardService;

#[Group('/public/classroom/institutions')]
class ClassroomDashboardController extends ExploreBaseController
{
    private ClassroomDashboardService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomDashboardService($this->pdo);
    }

    #[Route('/{institution_id}/dashboard', 'GET', ['api'])]
    public function getDashboard(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'institution_id' => 'required|integer',
            ],
        );

        $data = $this->service->getDashboard((int) $validated['institution_id']);

        $this->respond(
            [
                'status' => true,
                'data'   => $data,
            ],
            HttpStatus::OK
        );
    }
}
