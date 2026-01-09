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

    #[Route('', 'POST', ['api', 'auth'])]
    public function addNews(): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'date_posted' => 'nullable|date',
                'category_ids' => 'array|required|min:1',
                'author_id' => 'required|integer',
                'author_name' => 'required|string|max:100',
                'status' => 'required|string|in:draft,published,archived',

                'images' => 'required|array|min:1',
                'images.name' => 'required|array|min:1',
                'images.name.*' => 'required|string|filled',
                'images.type' => 'required|array|min:1',
                'images.type.*' => 'required|string|filled|in:image/jpeg,image/png,image/gif',
                'images.tmp_name' => 'required|array|min:1',
                'images.tmp_name.*' => 'required|string|filled',
                'images.size' => 'required|array|min:1',
                'images.error' => 'required|array|min:1',
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
                'success' => true,
                'message' => 'News item added successfully',
                'id' => $res
            ],
            HttpStatus::CREATED
        );
    }

    #[Route("/{id}/status", 'PUT', ['api', 'auth'])]
    public function updateNewsStatus(array $vars): void
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'status' => 'required|string|in:draft,published,archived',
            ]
        );

        $newsId = (int)$data['id'];
        $res = $this->newsService->updateNewsStatus($newsId, $data['status']);

        if (!$res) {
            $this->respondError(
                "Failed to update news status.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'News status updated successfully',
            ]
        );
    }

    #[Route('/admin', 'GET', ['api', 'auth'])]
    public function getNewsAdmin(): void
    {
        $newsItems = $this->newsService->getNewsAdmin();

        $this->respond(
            [
                'success' => true,
                'message' => 'News items retrieved successfully',
                'data' => $newsItems
            ]
        );
    }

    #[Route('', 'GET', ['api'])]
    public function getNews(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
            ]
        );

        $newsItems = $this->newsService->getNews($validated);

        $this->respond(
            [
                'success' => true,
                'message' => 'News items retrieved successfully',
                'data' => $newsItems
            ]
        );
    }

    #[Route('/{id:\d+}', 'DELETE', ['api', 'auth'])]
    public function deleteNews(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'id' => 'required|integer',
            ]
        );

        $newsId = (int)$validated['id'];
        $res = $this->newsService->deleteNews($newsId);

        if (!$res) {
            $this->respondError(
                "Failed to delete news item.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'News item deleted successfully',
            ]
        );
    }
}
