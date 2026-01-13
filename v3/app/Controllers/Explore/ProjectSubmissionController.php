<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProjectSubmissionService;

#[Group('/public/project')]
class ProjectSubmissionController extends ExploreBaseController
{
    private ProjectSubmissionService $projectSubmissionService;

    public function __construct()
    {
        parent::__construct();
        $this->projectSubmissionService = new ProjectSubmissionService($this->pdo);
    }

    #[Route('/submissions', 'POST', middleware: ['api'])]
    public function submitProject(): void
    {
        $validated = $this->validate(
            $this->getRequestData(),
            [
                'assignment' => 'required|array|min:1',
                'assignment.*.file_name' => 'required|string',
                'assignment.*.old_file_name' => 'nullable|string',
                'assignment.*.file' => 'required|string',
                'assignment.*.type' => 'required|string|in:pdf',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'quiz_score' => 'required|numeric|min:0|max:100',
            ]
        );

        $submissionId = $this->projectSubmissionService->submitProject($validated);

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
}
