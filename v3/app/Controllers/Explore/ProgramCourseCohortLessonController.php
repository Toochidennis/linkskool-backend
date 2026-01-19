<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProgramCourseCohortLessonService;

#[Group('/public/programs/{program_id}/courses/{course_id}/cohorts/{cohort_id}/lessons')]
class ProgramCourseCohortLessonController extends ExploreBaseController
{
    private ProgramCourseCohortLessonService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ProgramCourseCohortLessonService($this->pdo);
    }

    #[Route('', 'POST', ['api', 'auth'])]
    public function addLesson(array $vars)
    {
        $validated = $this->validate(
            data: [...$this->getRequestData(), ...$vars],
            rules: [
                'program_id' => 'required|integer|',
                'course_id' => 'required|integer|',
                'cohort_id' => 'required|integer|',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'goals' => 'nullable|string',
                'objectives' => 'nullable|string',
                'video_url' => 'required|string',
                'recorded_video_url' => 'nullable|string',
                'display_order' => 'required|integer',
                'write_up_content' => 'nullable|string',
                'assignment_instructions' => 'nullable|string',
                'assignment_due_date' => 'nullable|date',
                'is_final_lesson' => 'required|boolean',
                'author_name' => 'required|string|max:255',
                'author_id' => 'required|integer',
                'lesson_date' => 'required|date',

                //Files
                'certificate' => 'nullable|array',
                'certificate.name' => 'required_with:certificate|string',
                'certificate.tmp_name' => 'required_with:certificate|string',
                'certificate.error' => 'required_with:certificate|integer',

                'material' => 'required|array',
                'material.name' => 'required|string',
                'material.tmp_name' => 'required|string',
                'material.error' => 'required|integer',

                'assignment' => 'nullable|array',
                'assignment.name' => 'required_with:assignment|string',
                'assignment.tmp_name' => 'required_with:assignment|string',
                'assignment.error' => 'required_with:assignment|integer',
            ]
        );

        $success = $this->service->addLessonToCohort($validated);
        if (!$success) {
            $this->respondError(
                message: 'Failed to add lesson to cohort.',
                statusCode: HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            data: [
                'success' => true,
                'message' => 'Lesson added to cohort successfully.'
            ],
            statusCode: HttpStatus::CREATED
        );
    }

    #[Route('', 'GET', ['api', 'auth'])]
    public function getLessons(array $vars)
    {
        $validated = $this->validate(
            data: $vars,
            rules: [
                'cohort_id' => 'required|integer',
            ]
        );

        $lessons = $this->service->getLessonsByCohortId((int) $validated['cohort_id']);

        $this->respond(
            data: [
                'success' => true,
                'data' => $lessons
            ],
            statusCode: HttpStatus::OK
        );
    }

    #[Route('quiz', 'GET', ['api', 'auth'])]
    public function getLessonQuiz(array $vars)
    {
        $validated = $this->validate(
            data: $vars,
            rules: [
                'lesson_id' => 'required|integer',
            ]
        );

        $quiz = $this->service->getLessonQuiz((int) $validated['lesson_id']);

        $this->respond(
            data: [
                'success' => true,
                'data' => $quiz
            ],
            statusCode: HttpStatus::OK
        );
    }
}
