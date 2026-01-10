<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\ForYouService;

#[Group('/public')]
class ForYouController
{
    private ForYouService $forYouService;

    public function __construct()
    {
        $pdo = DatabaseConnector::connect();
        $this->forYouService = new ForYouService($pdo);
    }

    #[Route('/for-you', 'GET', ['api'])]
    public function forYou()
    {
        ResponseHandler::sendJsonResponse(
            [
                'success' => true,
                'data' => $this->forYouService->getList()
            ]
        );
    }
}
