<?php

namespace V3\App\Controllers\Portal\Results;

use V3\App\Controllers\BaseController;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Results\ResultCommentService;

#[Group('/portal')]
class ResultCommentController extends BaseController
{
    private ResultCommentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ResultCommentService($this->pdo);
    }

    #[Route(
        '/students/result/comment',
        'POST',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function store()
    {
        $data = $this->validate(
            $this->post,
            [
                'student_id' => 'required|integer',
                'comment' => 'required|string',
                'year' => 'required|integer',
                'term' => 'required|integer|in:1,2,3'
            ]
        );

        $commentId = $this->service->insertComment($data);

        if ($commentId) {
            return $this->respond([
                'success' => true,
                'message' => 'Comment added successfully.'
            ], HttpStatus::CREATED);
        }

        return $this->respondError(
            'Failed to add comment',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/students/result/comment/{id:\d+}',
        'PUT',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function update(array $vars)
    {
        $data = $this->validate(
            array_merge($vars, $this->post),
            [
                'id' => 'required|integer',
                'student_id' => 'required|integer',
                'comment' => 'required|string',
                'year' => 'required|integer',
                'term' => 'required|integer|in:1,2,3'
            ]
        );

        $updated = $this->service->updateComment($data);

        if ($updated) {
            return $this->respond([
                'success' => true,
                'message' => 'Comment updated successfully.'
            ], HttpStatus::CREATED);
        }

        return $this->respondError(
            'Failed to update comment.',
            HttpStatus::BAD_REQUEST
        );
    }
}
