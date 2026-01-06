<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\NewsService;
use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\ResponseHandler;

#[Group('/public/news')]
class NewsController extends ExploreBaseController
{
    private NewsService $newsService;

    public function __construct()
    {
        parent::__construct();
        $this->newsService = new NewsService($this->pdo);
    }

    #[Route("/news", 'POST', ['api', 'auth'])]
    public function addNews(): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'date_posted' => 'nullable|date',
                'category' => 'required|string|max:100',
                'author_id' => 'required|integer',
                'author_name' => 'required|string|max:100',
                'status' => 'required|string|in:draft,published,archived',

                'images' => 'required|array|min:1',
                'images.*.name' => 'required|string|filled',
                'images.*.type' => 'required|string|filled|in:image/jpeg,image/png,image/gif',
                'images.*.tmp_name' => 'required|string|filled',
            ]
        );

        $res = $this->newsService->addNews($data);

        if (!$res) {
            $this->respondError(
                "Failed to create news item.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'News item added successfully',
                'id' => $res
            ],
            HttpStatus::CREATED
        );
    }
}
