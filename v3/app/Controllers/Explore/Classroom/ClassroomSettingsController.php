<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomSettingsService;

#[Group('/public/classroom/settings')]
class ClassroomSettingsController extends ExploreBaseController
{
    private ClassroomSettingsService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomSettingsService($this->pdo);
    }

    #[Route('/levels', 'GET', ['api'])]
    public function getLevels(array $vars): void
    {
        $this->respond(
            [
                'status' => true,
                'data'   => $this->service->getLevels(),
            ],
            HttpStatus::OK
        );
    }

    #[Route('/subjects', 'GET', ['api'])]
    public function getSubjects(array $vars): void
    {
        $this->respond(
            [
                'status' => true,
                'data'   => $this->service->getSubjects(),
            ],
            HttpStatus::OK
        );
    }
}
