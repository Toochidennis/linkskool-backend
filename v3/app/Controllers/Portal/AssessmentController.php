<?php

namespace V3\App\Controllers\Portal;

use Exception;
use PDOException;
use V3\App\Utilities\HttpStatus;
use V3\App\Traits\ValidationTrait;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\AssessmentService;

class AssessmentController extends BaseController
{
    use ValidationTrait;

    private AssessmentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AssessmentService($this->pdo);
    }

    public function addAssessments()
    {
        $requiredFields = [
            'assessments',
            'assessments.*.assessment_name',
            'assessments.*.max_score',
            'assessments.*.level_id'
        ];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $inserted = $this->service->insertAssessment($data['assessments']);

            if ($inserted) {
                return $this->respond(
                    ['success' => true, 'message' => 'Assessment(s) added successfully'],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to add assessment');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateAssessment(array $vars) {}

    public function getAllAssessments()
    {
        try {
            $assessments = $this->service->getAssessments();
        } catch (PDOException $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getAssessmentByLevel(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['level_id']);
        try {
            $assessments = $this->service->getAssessments();
        } catch (PDOException $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function deleteAssessment(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);
        try {
            $delete = $this->service->deleteAssessment($data['id']);

            if ($delete) {
                return $this->respond(
                    ['success' => true, 'message' => 'Deleted assessment successfully']
                );
            }

            return $this->respondError('Failed to delete assessment');
        } catch (PDOException $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
