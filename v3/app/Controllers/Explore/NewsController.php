<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\NewsService;
use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;

#[Group('/public')]
class NewsController extends ExploreBaseController
{
    private NewsService $newsService;

    public function __construct()
    {
        parent::__construct();
        $this->newsService = new NewsService($this->pdo);
    }

    #[Route('/news', 'POST', ['api', 'auth'])]
    public function addNews(): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'date_posted' => 'nullable|date',
                'deadline' => 'nullable|date',
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

    #[Route('/news/{id:\d+}', 'POST', ['api', 'auth'])]
    public function updateNews(array $vars): void
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'date_posted' => 'nullable|date',
                'deadline' => 'nullable|date',
                'category_ids' => 'array|required|min:1',
                'author_id' => 'required|integer',
                'author_name' => 'required|string|max:100',
                'status' => 'required|string|in:draft,published,archived',
                'notify' => 'nullable|boolean',
                'old_images' => 'nullable|array|min:1',
                'old_images.*file_name' => 'nullable|string|filled',
                'old_images.*old_file_name' => 'nullable|string|filled',
                'old_images.*file' => 'nullable|string|filled',
                'old_images.*is_deleted' => 'nullable|boolean',

                'images' => 'nullable|array|min:1',
                'images.name' => 'nullable|array|min:1',
                'images.name.*' => 'nullable|string|filled',
                'images.type' => 'nullable|array|min:1',
                'images.type.*' => 'nullable|string|filled|in:image/jpeg,image/png,image/gif',
                'images.tmp_name' => 'nullable|array|min:1',
                'images.tmp_name.*' => 'nullable|string|filled',
                'images.size' => 'nullable|array|min:1',
                'images.error' => 'nullable|array|min:1',
            ]
        );

        $res = $this->newsService->updateNews($data);

        if (!$res) {
            $this->respondError(
                "Failed to update news item.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'News item updated successfully',
            ]
        );
    }

    #[Route(path: '/news/{id}/notify', method: 'PUT', middleware: ['api', 'auth'])]
    public function notifyNews(array $vars): void
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'notified_by' => 'required|integer',
            ]
        );

        $newsId = (int)$data['id'];
        $res = $this->newsService->notifyNews($newsId, $data['notified_by']);

        if (!$res) {
            $this->respondError(
                "Failed to notify news.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'News notified successfully',
            ]
        );
    }

    #[Route("/news/{id}/status", 'PUT', ['api', 'auth'])]
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

    #[Route('/news/admin', 'GET', ['api', 'auth'])]
    public function getNewsAdmin(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
            ]
        );

        $newsItems = $this->newsService->getNewsAdmin($validated);

        $this->respond(
            [
                'success' => true,
                'message' => 'News items retrieved successfully',
                'data' => $newsItems
            ]
        );
    }

    #[Route('/news', 'GET', ['api'])]
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

    #[Route('/news/{id:\d+}', 'DELETE', ['api', 'auth'])]
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
