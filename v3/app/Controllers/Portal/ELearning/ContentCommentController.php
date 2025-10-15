<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\ELearning\ContentCommentService;

#[Group('/portal')]
class ContentCommentController extends BaseController
{
    private ContentCommentService $contentCommentService;

    public function __construct()
    {
        parent::__construct();
        $this->contentCommentService = new ContentCommentService($this->pdo);
    }

    #[Route(
        '/elearning/{content_id:\d+}/comments',
        'POST',
        ['auth']
    )]
    public function store(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'content_title' => 'required|string',
                'content_id' => 'required|integer',
                'user_id' => 'required|integer',
                'user_name' => 'required|string',
                'comment' => 'required|string',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'term' => 'required|integer|in:1,2,3'
            ]
        );

        $newId = $this->contentCommentService->addComment($cleanedData);

        if ($newId) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'Comment added successfully',
                ],
                HttpStatus::CREATED
            );
        }

        $this->respondError(
            'Failed to add comment',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/elearning/comments/{id:\d+}',
        'PUT',
        ['auth']
    )]
    public function update(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'content_title' => 'required|string',
                'content_id' => 'required|integer',
                'user_id' => 'required|integer',
                'user_name' => 'required|string',
                'comment' => 'required|string',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'term' => 'required|integer|in:1,2,3'
            ]
        );

        $updated = $this->contentCommentService->updateComment($cleanedData);

        if ($updated) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'Comment updated successfully'
                ]
            );
        }

        $this->respondError(
            'Failed to update comment',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/elearning/{content_id:\d+}/comments',
        'GET',
        ['auth']
    )]
    public function getCommentsByContentId(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'content_id' => 'required|integer',
                'page' => 'required|integer|min:1',
                'limit' => 'required|integer|min:1|max:100'
            ]
        );

        $comments = $this->contentCommentService->getCommentsByContentId(
            $cleanedData['content_id'],
            $cleanedData['page'],
            $cleanedData['limit']
        );

        return $this->respond(
            [
                'success' => true,
                'response' => $comments
            ]
        );
    }

    #[Route(
        '/elearning/{syllabus_id:\d+}/comments/streams',
        'GET',
        ['auth']
    )]
    public function streams(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'syllabus_id' => 'required|integer',
            ]
        );

        $comments = $this->contentCommentService->getContentsComments(
            $cleanedData['syllabus_id'],
        );

        return $this->respond(
            [
                'success' => true,
                'data' => $comments
            ]
        );
    }

    #[Route(
        '/elearning/comments/{id:\d+}',
        'DELETE',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function delete(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
            ]
        );

        $deleted = $this->contentCommentService
            ->deleteComment($cleanedData['id'],);

        if ($deleted) {
            return $this->respond(
                [
                    'success' => true,
                    'message' => 'Comment deleted successfully'
                ]
            );
        }

        return $this->respondError(
            'Failed to delete comment',
            HttpStatus::BAD_REQUEST
        );
    }
}
