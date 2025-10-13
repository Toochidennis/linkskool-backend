<?php

namespace V3\App\Controllers\Portal\Results;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Academics\AssessmentService;

#[Group('/portal')]
class AssessmentController extends BaseController
{
    private AssessmentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AssessmentService($this->pdo);
    }

    #[Route('/assessments', 'POST', ['auth', 'role:admin'])]
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

        $inserted = $this->service->insertAssessment($data['assessments']);

        if ($inserted) {
            return $this->respond(
                ['success' => true, 'message' => 'Assessment(s) added successfully'],
                HttpStatus::CREATED
            );
        }

        return $this->respondError('Failed to add assessment');
    }

    #[Route('/assessments/{id:\d+}', 'PUT', ['auth', 'role:admin'])]
    public function updateAssessment(array $vars)
    {
        $requiredFields = ['id', 'assessment_name', 'max_score', 'level_id', 'type'];
        $data = $this->validateData($this->post + $vars, $requiredFields);

        $updated = $this->service->updateAssessment($data);

        if ($updated) {
            return $this->respond(
                ['success' => true, 'message' => 'Assessment updated successfully.']
            );
        }

        return $this->respondError('Failed to update assessment');
    }

    #[Route('/assessments', 'GET', ['auth', 'role:admin'])]
    public function getAllAssessments()
    {
        $assessments = $this->service->getAssessments();
        $transformed = $this->service->transformAssessment($assessments);

        if ($transformed) {
            return $this->respond(['success' => true, 'assessments' => $transformed]);
        }

        return $this->respondError('Failed to fetch assessments');
    }

    #[Route('/assessments/{level_id:\d+}', 'GET', ['auth', 'role:admin'])]
    public function getAssessmentByLevel(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['level_id']);

        $assessments = $this->service->getAssessments($data['level_id']);
        $transformed = $this->service->transformAssessment($assessments);

        if ($transformed) {
            return $this->respond(['success' => true, 'assessments' => $transformed]);
        }

        return $this->respondError('Failed to fetch assessments');
    }

    #[Route('/assessments/{id:\d+}', 'DELETE', ['auth', 'role:admin'])]
    public function deleteAssessment(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);
        $delete = $this->service->deleteAssessment($data['id']);

        if ($delete) {
            return $this->respond(
                ['success' => true, 'message' => 'Deleted assessment successfully']
            );
        }

        return $this->respondError('Failed to delete assessment');
    }
}
