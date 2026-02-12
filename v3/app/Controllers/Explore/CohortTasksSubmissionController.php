<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CohortTasksSubmissionService;

#[Group('/public/learning')]
class CohortTasksSubmissionController extends ExploreBaseController
{
    private CohortTasksSubmissionService $submissionService;

    public function __construct()
    {
        parent::__construct();
        $this->submissionService = new CohortTasksSubmissionService($this->pdo);
    }

    #[Route('/lessons/{lesson_id}/assignments', 'POST', middleware: ['api'])]
    public function submitProject(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'assignment' => 'nullable|array|min:1',
                'assignment.*.file_name' => 'required_with:assignment|string',
                'assignment.*.old_file_name' => 'nullable|string',
                'assignment.*.file' => 'required_with:assignment|string',
                'assignment.*.type' => 'required_with:assignment|string|in:pdf',
                'submission_type' => 'required|string|in:upload,text,link,mixed',
                'profile_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'lesson_id' => 'required|integer',
                'quiz_score' => 'required|numeric|min:0|max:100',
            ]
        );

        $submissionId = $this->submissionService->submitProject($validated);

        if (!$submissionId) {
            $this->respondError(
                'Failed to submit project.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Project submitted successfully.',
                'data' => [
                    'submission_id' => $submissionId,
                ],
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/lessons/{lesson_id}/assignments/{submission_id}/grade', 'POST', middleware: ['api', 'auth'])]
    public function gradeSubmission(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'assigned_score' => 'required|numeric|min:0|max:100',
                'remark' => 'nullable|string',
                'comment' => 'nullable|string',
                'graded_by' => 'required|integer',
            ]
        );

        $this->submissionService->gradeSubmission($validated);

        $this->respond([
            'success' => true,
            'message' => 'Submission graded successfully.'
        ]);
    }
}
