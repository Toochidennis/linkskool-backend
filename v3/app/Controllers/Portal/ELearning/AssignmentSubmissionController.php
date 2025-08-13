<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\AssignmentSubmissionService;

class AssignmentSubmissionController extends BaseController
{
    private AssignmentSubmissionService $assignmentSubmissionService;

    public function __construct()
    {
        parent::__construct();
        $this->assignmentSubmissionService = new AssignmentSubmissionService($this->pdo);
    }

    public function submit(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'assignment_id' => 'required|integer',
                'student_id' => 'required|integer',
                'student_name' => 'required|string|filled',
                'files' => 'required|array|min:1',
                'files.*.file_name' => 'required|string|filled',
                'files.*.type' => 'required|string|in:pdf,doc,docx',
                'files.*.old_file_name' => 'sometimes|string',
                'files.*.file' => 'required|string|filled',
                'mark' => 'required|numeric',
                'score' => 'required|numeric',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer',
                'class_id' => 'required|integer',
                'course_name' => 'required|string|filled',
                'class_name' => 'required|string|filled',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer'
            ]
        );

        try {
            $newId = $this->assignmentSubmissionService->submitAssignment($cleanedData);

            if ($newId) {
                return $this->respond(
                    [
                        'success' => true,
                        'message' => 'Assignment submitted successfully',
                    ],
                    HttpStatus::CREATED
                );
            } else {
                return $this->respondError(
                    'Failed to submit assignment',
                    HttpStatus::BAD_REQUEST
                );
            }
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to submit assignment: ' . $e->getMessage()
            );
        }
    }

    public function getSubmissions(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
            ]
        );

        try {
            $submissions = $this->assignmentSubmissionService
                ->getAssignmentSubmissions($cleanedData);

            return $this->respond(
                [
                    'success' => true,
                    'response' => $submissions
                ],
            );
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to retrieve submissions: ' . $e->getMessage()
            );
        }
    }

    public function getMarkedAssignment(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
                'student_id' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer',
            ]
        );

        try {
            $markedAssignment = $this->assignmentSubmissionService->getMarkedAssignment($filteredVars);

            return $this->respond(
                [
                    'success' => true,
                    'response' => $markedAssignment,
                ]
            );
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to retrieve quiz submissions: ' . $e->getMessage()
            );
        }
    }
}
