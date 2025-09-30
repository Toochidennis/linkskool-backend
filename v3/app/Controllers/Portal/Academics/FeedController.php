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

    public function addContent()
    {
        $filteredData = $this->validate(
            data: $this->post,
            rules: [
                "title" => "required|string|max:255",
                "type" => "required|string|in:news,reply,question",
                "parent_id" => "required_if:type,reply|integer",
                "content" => "required|string",
                "author_name" => "required|string",
                "author_id" => "required|integer",
                "files" => "nullable|array",
                "files.*.file_name" => "required|string",
                "files.*.type" => "required|string|in:image",
                "files.*.old_file_name" => "string",
                "files.*.file" => "required|string",
                "term" => "required|integer|in:1,2,3",
            ]
        );

        try {
            $contentId = $this->feedService->addContent($filteredData);

            if ($contentId) {
                $this->respond([
                    'success' => true,
                    'message' => 'Content added successfully',
                    "content_id" => $contentId,
                ], HttpStatus::CREATED);
            }

            $this->respondError('Failed to add content', HttpStatus::BAD_REQUEST);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function updateContent(array $vars)
    {
        $filteredData = $this->validate(
            data: array_merge($this->post, $vars),
            rules: [
                "news_id" => "required|integer",
                "title" => "required|string|max:255",
                "type" => "required|string|in:news,reply,question",
                "parent_id" => "required_if:type,reply|integer",
                "content" => "required|string",
                "author_name" => "required|string",
                "author_id" => "required|integer",
                "files" => "nullable|array",
                "files.*.file_name" => "string",
                "files.*.type" => "required|string|in:image",
                "files.*.old_file_name" => "required|string",
                "files.*.file" => "string",
                "term" => "required|integer|in:1,2,3",
            ]
        );

        try {
            $contentId = $this->feedService->updateContent($filteredData);

            if ($contentId) {
                $this->respond([
                    'success' => true,
                    'message' => 'Content updated successfully',
                ], HttpStatus::OK);
            }

            $this->respondError('Failed to update content', HttpStatus::BAD_REQUEST);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getContents(array $vars)
    {
        $filteredData = $this->validate(
            data: $vars,
            rules: [
                'term' => 'integer|in:1,2,3',
                "page" => "integer|min:1",
                "limit" => "integer|min:1|max:100",
            ]
        );

        try {
            $contents = $this->feedService->getContents($filteredData);
            $this->respond($contents, HttpStatus::OK);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function deleteContent(array $vars)
    {
        $filteredData = $this->validate(
            data: $vars,
            rules: [
                "news_id" => "required|integer",
            ]
        );

        try {
            $deleted = $this->feedService->deleteContent($filteredData['news_id']);

            if ($deleted) {
                $this->respond([
                    'success' => true,
                    'message' => 'Content deleted successfully',
                ], HttpStatus::OK);
            }

            $this->respondError('Failed to delete content', HttpStatus::BAD_REQUEST);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
