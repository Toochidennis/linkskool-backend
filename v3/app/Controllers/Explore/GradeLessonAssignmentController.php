<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\GradeLessonAssignmentService;

#[Group('/public')]
class GradeLessonAssignmentController extends ExploreBaseController
{
    private GradeLessonAssignmentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new GradeLessonAssignmentService($this->pdo);
    }

    #[Route(
        '/learn/lessons/{lesson_id}/submissions',
        'GET',
        ['api', 'auth']
    )]
    public function getLessonSubmissions(array $vars)
    {
        $validated = $this->validate(
            data: [...$this->getRequestData(), ...$vars],
            rules: [
                'lesson_id' => 'required|integer',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer',
            ]
        );

        $submissions = $this->service->getLessonSubmissions(
            (int) $validated['lesson_id'],
            (int)($validated['page'] ?? 1),
            (int)($validated['limit'] ?? 25)
        );

        $this->respond(
            data: [
                'success' => true,
                'data' => $submissions,
            ],
            statusCode: HttpStatus::OK
        );
    }

    #[Route(
        '/learn/lessons/submissions/auto-grade',
        'POST',
        ['api', 'auth']
    )]
    public function autoGradeSubmissions(array $vars)
    {
        $validated = $this->validate(
            data: [...$this->getRequestData(), ...$vars],
            rules: [
                'submission_ids' => 'required|array|min:1',
                'submission_ids.*' => 'required|integer',
                'graded_by' => 'required|integer',
                'batch_id' => 'nullable|string',
            ]
        );

        $results = $this->service->autoGradeSubmissions($validated);

        $this->respond(
            data: [
                'success' => true,
                'data' => $results,
            ],
            statusCode: HttpStatus::OK
        );
    }

    #[Route('/learn/lessons/submissions/grade', 'POST', middleware: ['api', 'auth'])]
    public function gradeSubmission(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'graded_by' => 'required|integer',
                'notify_student' => 'nullable|boolean',
                'results' => 'required|array|min:1',
                'results.*.submission_id' => 'required|integer',
                'results.*.score' => 'required|numeric|min:0|max:100',
                'results.*.comment' => 'nullable|string',
            ]
        );

        $updated = $this->service->gradeSubmissions($validated);

        if (!$updated) {
            $this->respondError(
                'Failed to grade submission.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            data: [
                'success' => true,
                'message' => 'Submission graded successfully.'
            ],
            statusCode: HttpStatus::OK
        );
    }

    #[Route('/learn/lessons/submissions/notify', 'POST', middleware: ['api', 'auth'])]
    public function notifyStudent(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'submission_ids' => 'required|array|min:1',
                'submission_ids.*' => 'required|integer',
                'graded_by' => 'required|integer',
            ]
        );

        $this->service->notifyStudents(
            $validated['submission_ids'],
            $validated['graded_by']
        );

        $this->respond(
            data: [
                'success' => true,
                'message' => 'Student notified successfully.'
            ],
            statusCode: HttpStatus::OK
        );
    }
}
