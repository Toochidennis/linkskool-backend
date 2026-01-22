<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CohortLessonQuizService;
use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;

#[Group(prefix: 'public/cohorts/{cohort_id}/lessons/{lesson_id}/quizzes')]
class CohortLessonQuizController extends ExploreBaseController
{
    private CohortLessonQuizService $service;

    public function __construct(\PDO $pdo)
    {
        parent::__construct();
        $this->service = new CohortLessonQuizService($pdo);
    }

    #[Route('', 'POST', ['api', 'auth'])]
    public function create(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'lesson_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'cohort_id' => 'required|integer',
                'question_text' => 'required|string',
                'options' => 'required|array',
                'options.*.text' => 'required|string',
                'correct' => 'required|array',
                'correct.text' => 'required|string',
                'correct.order' => 'required|integer',
            ]
        );

        $result = $this->service->create($validatedData);

        if (!$result) {
            $this->respondError(
                'Failed to create quiz question.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Quiz question created successfully.',
                'data' => ['id' => $result],
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{question_id}', 'PUT', ['api', 'auth'])]
    public function update(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'question_id' => 'required|integer',
                'lesson_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
                'course_name' => 'required|string',
                'cohort_id' => 'required|integer',
                'question_text' => 'required|string',
                'options' => 'required|array',
                'options.*.text' => 'required|string',
                'correct' => 'required|array',
                'correct.text' => 'required|string',
                'correct.order' => 'required|integer',
            ]
        );

        $result = $this->service->update($validatedData);

        if (!$result) {
            $this->respondError(
                'Failed to update quiz question.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Quiz question updated successfully.',
                'data' => ['id' => $result],
            ],
            HttpStatus::OK
        );
    }

    #[Route('', 'GET', ['api'])]
    public function getByQuizLessonId(array $vars)
    {
        $lessonId = (int) $vars['lesson_id'];
        $quizzes = $this->service->getQuizByLessonId($lessonId);

        $this->respond(
            [
                'success' => true,
                'data' => $quizzes,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{question_id}', 'DELETE', ['api', 'auth'])]
    public function delete(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'question_id' => 'required|integer',
            ]
        );

        $result = $this->service->delete($validatedData['question_id']);

        if (!$result) {
            $this->respondError(
                'Failed to delete quiz question.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Quiz question deleted successfully.',
            ],
            HttpStatus::OK
        );
    }
}
