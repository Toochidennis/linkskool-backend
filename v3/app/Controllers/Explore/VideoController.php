<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\VideoService;

#[Group("/public")]
class VideoController
{
    private VideoService $videoService;

    public function __construct()
    {
        $pdo = DatabaseConnector::connect();
        $this->videoService = new VideoService($pdo);
    }

    #[Route('/videos', 'GET', ['api'])]
    public function index()
    {
        ResponseHandler::sendJsonResponse(
            $this->videoService->getVideos()
        );
    }
}
