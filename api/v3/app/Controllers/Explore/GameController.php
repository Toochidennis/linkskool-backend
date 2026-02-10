<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Services\Explore\GameService;

#[Group("/public")]
class GameController
{
    private GameService $gameService;

    public function __construct()
    {
        $this->gameService = new GameService();
    }

    #[Route('/games', 'GET', ['api'])]
    public function index()
    {
        ResponseHandler::sendJsonResponse(
            $this->gameService->getList()
        );
    }
}
