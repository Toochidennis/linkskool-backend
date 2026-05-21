<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomSyncService;

#[Group('/public/classroom/sync')]
class ClassroomSyncController extends ExploreBaseController
{
    private ClassroomSyncService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomSyncService($this->pdo);
    }

    #[Route('/{institution_id}', 'GET', ['api'])]
    public function pull(array $vars): void
    {
        $validated = $this->validate($vars, [
            'institution_id' => 'required|string',
        ]);

        $data = $this->service->pull($validated['institution_id']);

        $this->respond([
            'status' => true,
            'data'   => $data,
        ], HttpStatus::OK);
    }

    #[Route('/{institution_id}', 'POST', ['api'])]
    public function push(array $vars): void
    {
        // $validated = $this->validate($vars, [
        //     'institution_id' => 'required|string',
        // ]);

        $counts = $this->service->push($this->getRequestData());

        $this->respond([
            'status'  => true,
            'message' => 'Sync complete.',
            'data'    => $counts,
        ], HttpStatus::OK);
    }
}
