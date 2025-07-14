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
        $data = $this->validate(
            data: $this->post,
            rules: [
                'title' => 'required|string|filled',
                'description' => 'required|string|filled',
                'topic' => 'sometimes|string',
                'topic_id' => 'sometimes|integer',
                'syllabus_id' => 'required|integer',
                'creator_name' => 'required|string|filled',
                'creator_id' => 'required|integer',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled',
                'files' => 'sometimes|array',
                'files.*.file_name' => 'required|string|filled',
                'files.*.old_file_name' => 'sometimes|string',
                'files.*.type' => 'required|string|filled',
                'files.*.file' => 'sometimes|string',
                'start_date' => 'required|string|filled',
                'end_date' => 'required|string|filled',
                'grade' => 'required|numeric',
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
        $data = $this->validate(
            data: $this->post + $vars,
            rules: [
                'id' => 'required|integer',
                'title' => 'required|string|filled',
                'description' => 'required|string|filled',
                'topic' => 'sometimes|string|filled',
                'topic_id' => 'sometimes|integer',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled',
                'files' => 'sometimes|array',
                'files.*.file_name' => 'sometimes|string',
                'files.*.old_file_name' => 'required|string|filled',
                'files.*.type' => 'required|string|filled',
                'files.*.file' => 'sometimes|string',
                'start_date' => 'required|string|filled',
                'end_date' => 'required|string|filled',
                'grade' => 'required|numeric',
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
        $data = $this->validate(data: $vars, rules: ['id' => 'required|integer']);

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
