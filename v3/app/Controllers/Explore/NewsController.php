<?php

namespace V3\App\Controllers\Explore;

use V3\App\Services\Explore\NewsService;
use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\ResponseHandler;

#[Group('/public')]
class NewsController
{
    private NewsService $newsService;

    public function __construct()
    {
        $this->newsService = new NewsService();
    }

    #[Route("/news", 'GET'['api'])]
    public function index()
    {
        ResponseHandler::sendJsonResponse(
            $this->newsService->getNews()
        );
    }
}
