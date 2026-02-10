<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ExamTypeService;

#[Group('/public')]
class ExamTypeController extends ExploreBaseController
{
    private ExamTypeService $examTypeService;

    public function __construct()
    {
        parent::__construct();
        $this->examTypeService = new ExamTypeService($this->pdo);
    }

    #[Route('/exam-types', 'POST', ['api', 'auth', 'role:admin'])]
    public function storeExamType(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'name' => 'required|string|filled',
            'shortname' => 'required|string|filled',
            'course_ids' => 'required|array',
            'is_active' => 'required|integer|in:0,1',
            'display_order' => 'required|integer|min:0'
        ]);

        $examTypeId = $this->examTypeService->createExamType($data);

        if ($examTypeId <= 0) {
            $this->respondError('Failed to create exam type.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Exam type created successfully.',
            'exam_type_id' => $examTypeId
        ]);
    }

    #[Route('/exam-types/{id:\d+}', 'PUT', ['api', 'auth', 'role:admin'])]
    public function updateExamType(array $vars): void
    {
        $data = $this->validate([...$this->getRequestData(), ...$vars], [
            'id' => 'required|integer',
            'name' => 'required|string|filled',
            'shortname' => 'required|string|filled',
            'course_ids' => 'required|array',
            'is_active' => 'required|integer|in:0,1',
            'display_order' => 'required|integer|min:0'
        ]);

        $updated = $this->examTypeService->updateExamType($data);

        if (!$updated) {
            $this->respondError(
                'Failed to update exam type.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Exam type updated successfully.'
        ]);
    }

    #[Route('/exam-types/active', 'GET', ['api', 'auth'])]
    public function getActiveExamTypes(array $vars): void
    {
        $this->respond([
            'success' => true,
            'data' => $this->examTypeService->getAllExamTypes(1)
        ]);
    }

    #[Route('/exam-types', 'GET', ['api', 'auth'])]
    public function getAllExamTypes(): void
    {
        $this->respond([
            'success' => true,
            'data' => $this->examTypeService->getAllExamTypes(0)
        ]);
    }


    #[Route('/exam-types/{id:\d+}', 'DELETE', ['api', 'auth', 'role:admin'])]
    public function deleteExamType(array $vars): void
    {
        $data = $this->validate($vars, [
            'id' => 'required|integer',
        ]);

        $deleted = $this->examTypeService->deleteExamType($data['id']);

        if (!$deleted) {
            $this->respondError('Failed to delete exam type.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Exam type deleted successfully.'
        ]);
    }
}
