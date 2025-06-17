<?php

namespace V3\App\Controllers\Portal\Results;

use Exception;
use PDOException;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\AssessmentService;

class AssessmentController extends BaseController
{
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
            'assessments.*.level_id',
            'assessments.*.type'
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

    public function updateAssessment(array $vars)
    {
        $requiredFields = ['id', 'assessment_name', 'max_score', 'level_id', 'type'];
        $data = $this->validateData($this->post + $vars, $requiredFields);

        try {
            $updated = $this->service->updateAssessment($data);

            if ($updated) {
                return $this->respond(
                    ['success' => true, 'message' => 'Assessment updated successfully.']
                );
            }

            return $this->respondError('Failed to update assessment');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getAllAssessments()
    {
        try {
            $assessments = $this->service->getAssessments();
            $transformed = $this->service->transformAssessment($assessments);

            if ($transformed) {
                return $this->respond(['success' => true, 'assessments' => $transformed]);
            }

            return $this->respondError('Failed to fetch assessments');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getAssessmentByLevel(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['level_id']);

        try {
            $assessments = $this->service->getAssessments($data['level_id']);
            $transformed = $this->service->transformAssessment($assessments);

            if ($transformed) {
                return $this->respond(['success' => true, 'assessments' => $transformed]);
            }

            return $this->respondError('Failed to fetch assessments');
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
