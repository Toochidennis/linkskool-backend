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
        $data = $this->validate(
            $this->post,
            [
                'assessments' => 'required|array|min:1',
                'assessments.*.assessment_name' => 'required|string',
                'assessments.*.max_score' => 'required|integer',
                'assessments.*.level_id' => 'required|integer|min:1',
                'assessments.*.type' => 'required|integer'
            ]
        );

        $inserted = $this->service->insertAssessment($data['assessments']);

        if ($inserted) {
            return $this->respond(
                ['success' => true, 'message' => 'Assessment(s) added successfully'],
                HttpStatus::CREATED
            );
        }

        return $this->respondError(
            'Failed to add assessment',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('/assessments/{id:\d+}', 'PUT', ['auth', 'role:admin'])]
    public function updateAssessment(array $vars)
    {
        $data = $this->validate(
            array_merge($this->post, $vars),
            [
                'id' => 'required|integer|min:1',
                'assessment_name' => 'required|string',
                'max_score' => 'required|integer|min:1',
                'level_id' => 'required|integer|min:1',
                'type' => 'required|integer'
            ]
        );

        $updated = $this->service->updateAssessment($data);

        if ($updated) {
            return $this->respond(
                ['success' => true, 'message' => 'Assessment updated successfully.',]
            );
        }

        return $this->respondError(
            'Failed to update assessment',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('/assessments', 'GET', ['auth', 'role:admin'])]
    public function getAllAssessments()
    {
        $assessments = $this->service->getAssessments();
        $transformed = $this->service->transformAssessment($assessments);

        if ($transformed) {
            return $this->respond(['success' => true, 'assessments' => $transformed]);
        }

        return $this->respondError(
            'Failed to fetch assessments',
            HttpStatus::BAD_REQUEST
        );
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

        return $this->respondError(
            'Failed to fetch assessments',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('/assessments/{id:\d+}', 'DELETE', ['auth', 'role:admin'])]
    public function deleteAssessment(array $vars)
    {
        $data = $this->validate(data: $vars,  rules: ['id' => 'required|integer']);
        $delete = $this->service->deleteAssessment($data['id']);

        if ($delete) {
            return $this->respond(
                ['success' => true, 'message' => 'Deleted assessment successfully']
            );
        }

        return $this->respondError(
            'Failed to delete assessment',
            HttpStatus::BAD_REQUEST
        );
    }
}
