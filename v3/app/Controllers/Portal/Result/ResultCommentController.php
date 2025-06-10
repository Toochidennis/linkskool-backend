<?php

namespace V3\App\Controllers\Portal\Result;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Results\ResultCommentService;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\HttpStatus;

class ResultCommentController extends BaseController
{
    use ValidationTrait;

    private ResultCommentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ResultCommentService($this->pdo);
    }

    public function store()
    {
        $data = $this->validateData(
            $this->post,
            ['student_id', 'comment', 'role', 'year', 'term']
        );

        try {
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
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        $data = $this->validateData($vars, ['id', 'comment']);

        try {
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
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
