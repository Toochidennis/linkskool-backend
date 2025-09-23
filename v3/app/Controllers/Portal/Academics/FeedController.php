<?php

namespace V3\App\Controllers\Portal\Academics;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\FeedService;

class FeedController extends BaseController
{
    private FeedService $feedService;

    public function __construct()
    {
        parent::__construct();
        $this->feedService = new FeedService($this->pdo);
    }

    public function addNews()
    {
        $filteredData = $this->validate(
            data: $this->post,
            rules: [
                "title" => "required|string|max:255",
                "content" => "required|string",
                "author_name" => "required|string",
                "author_id" => "required|integer",
                "files" => "nullable|array",
                "files.*.file_name" => "required|string",
                "files.*.file_type" => "required|string|in:image",
                "files.*.content" => "required|string",
                "term" => "required|integer|in:1,2,3",
                "published_at" => "required|date"
            ]
        );

        try {
            $newsId = $this->feedService->addNews($filteredData);

            if ($newsId) {
                $this->respond([
                    'success' => true,
                    'message' => 'News added successfully',
                    "news_id" => $newsId,
                ], HttpStatus::CREATED);
            }

            $this->respondError('Failed to add news', HttpStatus::BAD_REQUEST);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
