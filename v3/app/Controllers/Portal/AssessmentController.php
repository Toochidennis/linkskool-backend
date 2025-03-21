<?php

namespace V3\App\Controllers\Portal;

use Exception;
use PDOException;
use V3\App\Utilities\HttpStatus;
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
    }

    public function addAssessment()
    {
        $requiredFields = ['assesment_name', 'max_score', 'level'];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $assessmentId = $this->assessment->insert($data);

            $this->response = $assessmentId ? [
                'success' => true,
                'message' => 'Assessment added successfully.',
                'assessment_id' => $assessmentId
            ] : [
                'success' => false,
                'message' => 'Failed to add assessment'
            ];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateAssessment() {}

    public function fetchAssessments(array $params)
    {
        $requiredFields = ['level'];
        $data = $this->validateData($params, $requiredFields);

        try {
            $assessments = $this->assessment
                ->select(
                    columns: [
                        'assessment_table.id',
                        'assessment_table.assesment_name AS assessment_name',
                        'assessment_table.max_score',
                        'assessment_table.type',
                        'level_table.id AS level_id',
                        'level_table.level_name'
                    ]
                )
                ->join('level_table', 'assessment_table.level = level_table.id')
                ->where('level', '=', $data['level_id'])
                ->get();

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
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function deleteAssessment(array $params) {}
}
