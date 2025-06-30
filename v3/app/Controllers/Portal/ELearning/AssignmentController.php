<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\AssignmentService;

class AssignmentController extends BaseController
{
    private AssignmentService $assignmentService;

    public function __construct()
    {
        parent::__construct();
        $this->assignmentService = new AssignmentService($this->pdo);
    }

    public function store()
    {
        $data = $this->validateData(
            data: $this->post,
            requiredFields: [
                'title',
                'description',
                'topic',
                'topic_id',
                'syllabus_id',
                'creator_name',
                'creator_id',
                'classes.*.id',
                'classes.*.name',
                'files.*.file_name',
                'files.*.type',
                'files.*.file',
                'start_date',
                'end_date',
                'grade',
            ]
        );

        try {
            $id = $this->assignmentService->addAssignment($data);

            if ($id > 0) {
                return $this->respond(
                    data: [
                        'success' => true,
                        'message' => 'Assignment added successfully.',
                        'id' => $id
                    ],
                    statusCode: HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to add material');
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        $data = $this->validateData(
            data: $this->post + $vars,
            requiredFields: [
                'id',
                'title',
                'description',
                'topic',
                'topic_id',
                'classes.*.id',
                'classes.*.name',
                'files.*.type',
                'start_date',
                'end_date',
                'grade',
            ]
        );

        try {
            $id = $this->assignmentService->updateAssignment($data);
            if ($id > 0) {
                return $this->respond(
                    data: [
                        'success' => true,
                        'message' => 'Assignment updated successfully.',
                        'assignmentId' => $id
                    ]
                );
            }

            return $this->respondError('Failed to update material');
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function delete(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);

        try {
            $id = $this->assignmentService->deleteAssignment($data['id']);

            if ($id > 0) {
                return $this->respond(
                    data: [
                        'success' => true,
                        'message' => 'Assignment deleted successfully.',
                        'assignmentId' => $id
                    ]
                );
            }

            return $this->respondError('Assignment not found or already deleted.');
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
