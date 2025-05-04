<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Traits\PermissionTrait;
use V3\App\Traits\ValidationTrait;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ResultService;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;

class ResultController extends BaseController
{
    use ValidationTrait;
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

            return $this->respondError('Failed to add course results.');
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getResultTermsByStudent(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);

        try {
            $terms = $this->service->getResultTerms($data);
            return $this->respond(['success' => true, 'result_terms' => $terms]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
