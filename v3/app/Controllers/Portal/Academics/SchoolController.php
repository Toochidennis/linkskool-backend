<?php

namespace V3\App\Controllers\Portal\Academics;

use V3\App\Common\Routing\{Route, Group};
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Portal\Academics\SchoolService;

#[Group('/portal')]
class SchoolController
{
    private SchoolService $schoolService;
    public function __construct()
    {
        $this->schoolService = new SchoolService(DatabaseConnector::connect());
    }

    #[Route('/schools', 'GET', ['api'])]
    public function getSchools()
    {
        ResponseHandler::sendJsonResponse([
            'success' => true,
            'data' => $this->schoolService->getSchools()
        ]);
    }
}
