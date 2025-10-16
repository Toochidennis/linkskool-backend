<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Services\Explore\BookService;

#[Group("/public")]
class BookController
{
    private BookService $bookService;

    public function __construct()
    {
        $this->bookService = new BookService();
    }

    #[Route('/books', 'GET', ['api'])]
    public function index()
    {
        ResponseHandler::sendJsonResponse(
            $this->bookService->getList()
        );
    }
}
