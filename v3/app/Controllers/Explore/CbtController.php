<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\CbtService;

#[Group("/public/cbt")]
class CbtController
{
    private CbtService $cbtService;

    public function __construct()
    {
        $pdo = DatabaseConnector::connect();
        $this->cbtService = new CbtService($pdo);
    }

    #[Route('/exam-types', 'GET', ['api'])]
    public function index()
    {
        ResponseHandler::sendJsonResponse(
            response: [
                'success' => true,
                'data' => $this->cbtService->getFormattedExamHierarchy()
            ]
        );
    }

    #[Route('/exam-types/{id:\d+}', 'GET', ['api'])]
    public function getQuestions(array $vars)
    {

    }
}
