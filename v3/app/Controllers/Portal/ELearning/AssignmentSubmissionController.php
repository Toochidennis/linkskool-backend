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
                'files.*.name' => 'required|string|filled',
                'files.*.type' => 'required|string|in:pdf,doc,docx',
                'files.*.size' => 'required|integer|max:10485760',
                'mark' => 'required|numeric',
                'score' => 'required|numeric',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer',
                'class_id' => 'required|integer',
                'course_name' => 'required|string|filled',
                'class_name' => 'required|string|filled',
                'term' => 'required|string|in:1,2,3',
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
                        'id' => $newId
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
                'content_id' => 'required|integer',
                'year' => 'required|integer'
            ]
        );

        try {
            $submissions = $this->assignmentSubmissionService
                ->getAssignmentSubmissions($cleanedData);

            return $this->respond(
                [
                    'success' => true,
                    'data' => $submissions
                ],
                HttpStatus::OK
            );
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to retrieve submissions: ' . $e->getMessage()
            );
        }
    }
}
