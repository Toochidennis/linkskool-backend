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
                'submission_type' => 'required|string|in:upload,text,link',
                'text_content' => 'required_if:submission_type,text|string',
                'link_url' => 'required_if:submission_type,link|url',
                'profile_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'lesson_id' => 'required|integer',
                'quiz_score' => 'nullable|numeric|min:0|max:100',
            ]
        );

        $submissionId = $this->submissionService->submitProject($validated);

        if (!$submissionId) {
            $this->respondError(
                'Failed to submit project.',
                HttpStatus::BAD_REQUEST
            );
        }

        $submissionId = (int) $submissionId;
        $gradedBy = (int) $validated['profile_id'];

        register_shutdown_function(function () use ($submissionId, $gradedBy) {
            if (function_exists('fastcgi_finish_request')) {
                @fastcgi_finish_request();
            }

            ignore_user_abort(true);
            @set_time_limit(0);
            $this->submissionService->autoGradeSubmission($submissionId, $gradedBy);
        });

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
