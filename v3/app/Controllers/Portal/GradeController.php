<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Models\Portal\Grade;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;

class GradeController extends BaseController
{

    use ValidationTrait;
    private Grade $grade;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->grade = new Grade(pdo: $this->pdo);
    }

    public function addGrade()
    {
        $requiredFields = ['grade_symbol', 'start', 'remark'];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $assessmentId = $this->grade->insert($data);

            $this->response = $assessmentId ? [
                'success' => true,
                'message' => 'Grade added successfully.',
                'assessment_id' => $assessmentId
            ] : [
                'success' => false,
                'message' => 'Failed to add grade'
            ];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function fetchGrades()
    {
        try {
            $result = $this->grade->get();

            $grades  = array_map(fn($row) => [
                'id' => $row['id'],
                'grade_symbol' => $row['grade_symbol'],
                'start' => $row['start'],
                'remark' => $row['remark']
            ], $result);

            $this->response = ['success' => true, 'grades' => $grades];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function deleteGrade(array $params) {}
}
