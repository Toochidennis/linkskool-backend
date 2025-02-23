<?php

namespace V3\App\Controllers\Portal;

use Exception;
use PDOException;
use V3\App\Models\Portal\Assessment;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\AssessmentService;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\ResponseHandler;

class AssessmentController extends BaseController
{
    use ValidationTrait;
    private Assessment $assessment;
    private AssessmentService $assessmentService;


    public function __construct()
    {
        parent::__construct();
    }

    private function initialize()
    {
        $this->assessment = new Assessment($this->pdo);
        $this->assessmentService = new AssessmentService();
    }

    public function addAssessment()
    {
        $requiredFields = [
            'assessment_name',
            'max_score',
            'level'
        ];

        try {
            $data = $this->validateData($this->post, $requiredFields);
        } catch (\InvalidArgumentException $e) {
            http_response_code(response_code: 400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        try {
            $assessmentId = $this->assessment->insertAssessment($data);

            $this->response = $assessmentId ? [
                'success' => true,
                'message' => 'Assessment added successfully.',
                'assessment_id' => $assessmentId
            ] : [
                'success' => false,
                'message' => 'Failed to add assessment',
                'assessment_id' => $assessmentId
            ];
        } catch (Exception $e) {
            http_response_code(response_code: 500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateAssessment() {}

    public function fetchAssessments(array $params) {}

    public function deleteAssessment(array $params) {}
}
