<?php

namespace V3\App\Controllers\Portal\Academics;

use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Portal\Academics\SchoolService;

class SchoolController
{
    private SchoolService $schoolService;
    public function __construct()
    {
        $this->schoolService = new SchoolService(DatabaseConnector::connect());
    }

    public function getSchools()
    {
        try {
            ResponseHandler::sendJsonResponse([
                'success' => true,
                'data' => $this->schoolService->getSchools()
            ]);
        } catch (\Exception $e) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
