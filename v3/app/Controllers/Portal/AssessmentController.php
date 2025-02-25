<?php

namespace V3\App\Controllers\Portal;

use PDOException;
use V3\App\Traits\ValidationTrait;
use V3\App\Models\Portal\Assessment;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\AssessmentService;

class AssessmentController extends BaseController
{
    use ValidationTrait;
    private Assessment $assessment;
    private AssessmentService $assessmentService;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->assessment = new Assessment($this->pdo);
        $this->assessmentService = new AssessmentService();
    }

    public function addAssessment()
    {
        $data = $this->validateData($this->post, $this->assessment->requiredFields);

        try {
            $assessmentId = $this->assessment->insertAssessment($data);

            $this->response = $assessmentId ? [
                'success' => true,
                'message' => 'Assessment added successfully.',
                'assessment_id' => $assessmentId
            ] : [
                'success' => false,
                'message' => 'Failed to add assessment'
            ];
        } catch (\Exception $e) {
            http_response_code(response_code: 500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateAssessment() {
       
    }

    public function fetchAssessments(array $params)
    {
        // $requiredFields = ['level'];

        // try {
        //     $data = $this->validateData($this->post, $requiredFields);
        // } catch (\InvalidArgumentException $e) {
        //     http_response_code(response_code: 400);
        //     $this->response['message'] = $e->getMessage();
        //     ResponseHandler::sendJsonResponse(response: $this->response);
        // }

        try {
            $assessments = $this->assessment->fetchAssessments(
                columns: [
                    'assessment_table.id',
                    'assessment_table.assesment_name AS assessment_name',
                    'assessment_table.max_score',
                    'assessment_table.type',
                    'level_table.id AS level_id',
                    'level_table.level_name'
                ],
                joins: [
                    [
                        'table' => 'level_table',
                        'condition' => 'assessment_table.level = level_table.id',
                        'type' => 'INNER'
                    ]
                ],
            );

            $formatted = [];

            foreach ($assessments as $assessment) {
                $levelName = $assessment['level_name'];
                $levelId = $assessment['level_id'];

                if (!isset($formatted[$levelName])) {
                    $formatted[$levelName] = [];
                }

                if (!isset($formatted[$levelName]['level_id'])) {
                    $formatted[$levelName] = ['level_id' => $levelId, 'level_name' => $levelName];
                }

                $formatted[$levelName]['assessments'][] = [
                    'id' => $assessment['id'],
                    'assessment_name' => $assessment['assessment_name'],
                    'type' => $assessment['type']
                ];
            }

            $this->response = ['success' => true, 'response' => $formatted];
        } catch (PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function deleteAssessment(array $params) {}
}
