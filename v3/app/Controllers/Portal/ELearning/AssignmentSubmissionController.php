<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\ELearning\AssignmentSubmissionService;

#[Group('/portal')]
class AssignmentSubmissionController extends BaseController
{
    private AssignmentSubmissionService $assignmentSubmissionService;

    public function __construct()
    {
        parent::__construct();
        $this->assignmentSubmissionService = new AssignmentSubmissionService($this->pdo);
    }

    #[Route(
        '/students/{student_id:\d+}/assignment-submissions',
        'POST',
        ['auth', 'role:student']
    )]
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

        $newId = $this->assignmentSubmissionService->submitAssignment($cleanedData);

        if ($newId) {
            return $this->respond(
                [
                    'success' => true,
                    'message' => 'Assignment submitted successfully',
                ],
                HttpStatus::CREATED
            );
        }
        return $this->respondError(
            'Failed to submit assignment',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('/elearning/assignment/mark', 'PUT', ['auth', 'role:admin', 'role:staff'])]
    public function markAssignment(array $vars)
    {
        $filteredVars = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'score' => 'required|numeric',
            ]
        );

        $result = $this->assignmentSubmissionService
            ->markAssignment($filteredVars);

        if ($result) {
            return $this->respond(
                [
                    'success' => true,
                    'message' => 'Assignment marked successfully',
                ]
            );
        }

        return $this->respondError(
            'Failed to mark assignment',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/elearning/assignment/{id:\d+}/submissions',
        'GET',
        ['auth', 'role:admin', 'role:staff']
    )]
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

        $submissions = $this->assignmentSubmissionService
            ->getAssignmentSubmissions($cleanedData);

        return $this->respond(
            [
                'success' => true,
                'response' => $submissions
            ],
        );
    }

    #[Route(
        '/students/{student_id:\d+}/assignment-submissions',
        'GET',
        ['auth', 'role:student']
    )]
    public function getMarkedAssignment(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: [
                'content_id' => 'required|integer',
                'student_id' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer',
            ]
        );

        $markedAssignment = $this->assignmentSubmissionService
            ->getMarkedAssignment($filteredVars);

        return $this->respond(
            [
                'success' => true,
                'response' => $markedAssignment,
            ]
        );
    }

    #[Route(
        '/elearning/assignment/{content_id:\d+}/publish',
        'PUT',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function publishAssignment(array $vars)
    {
        $filteredVars = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'content_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'publish' => 'required|integer|in:0,1',
            ]
        );

        $result = $this->assignmentSubmissionService
            ->publishAssignment($filteredVars);

        if ($result) {
            return $this->respond(
                [
                    'success' => true,
                    'message' => 'Assignment published successfully',
                ]
            );
        }

        return $this->respondError(
            'Failed to publish assignment',
            HttpStatus::BAD_REQUEST
        );
    }
}
