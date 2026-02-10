<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CohortLessonProgressService;

#[Group('/public/learning/cohorts/{cohort_id}/lessons/{lesson_id}/progress')]
class CohortLessonProgressController extends ExploreBaseController
{
    private CohortLessonProgressService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CohortLessonProgressService($this->pdo);
    }

    #[\V3\App\Common\Routing\Route(
        '',
        'POST',
        ['api', 'auth']
    )]
    public function updateProgress(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'cohort_id' => 'required|integer',
                'lesson_id' => 'required|integer',
                'profile_id' => 'required|integer',
                'course_id' => 'required|integer',
                'program_id' => 'required|integer',
            ]
        );

        $result = $this->service->markLessonAsCompleted($validatedData);

        if (!$result) {
            $this->respondError(
                'Failed to update lesson progress.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Lesson progress updated successfully.',
                'data' => ['progress_id' => $result],
            ],
            HttpStatus::OK
        );
    }
}
