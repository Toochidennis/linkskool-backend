<?php

namespace V3\App\Controllers\Portal\Results;

use Exception;
use V3\App\Common\Traits\PermissionTrait;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ResultService;
use V3\App\Common\Utilities\HttpStatus;

class ResultController extends BaseController
{
    use PermissionTrait;

    private ResultService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ResultService($this->pdo);
    }

    public function updateResult()
    {
        $requiredFields = [
            'course_results',
            'course_results.*.result_id',
            'course_results.*.staff_id',
            'course_results.*.assessments',
        ];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $updated = $this->service->updateRecord($data['course_results']);
            if ($updated) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Course results added successfully.'
                ]);
            }

            return $this->respondError('Failed to add course results.', HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
