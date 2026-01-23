<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CohortLessonQuizService;
use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;

#[Group(prefix: '/public')]
class CohortLessonQuizController extends ExploreBaseController
{
    private CohortLessonQuizService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CohortLessonQuizService($this->pdo);
    }

    #[Route(
        '/learn/programs/lessons/{lesson_id}/quizzes',
        'POST',
        ['api', 'auth']
    )]
    public function create(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'question_id' => 'nullable|integer',
                'lesson_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
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
                'data' => ['question_id' => $result],
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/learn/programs/lessons/{lesson_id}/quizzes/{question_id}', 'PUT', ['api', 'auth'])]
    public function update(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'question_id' => 'required|integer',
                'lesson_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
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
                'data' => ['question_id' => $result],
            ],
            HttpStatus::OK
        );
    }

    #[Route('/learn/programs/lessons/{lesson_id}/quizzes', 'GET', ['api'])]
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

    #[Route('/learn/programs/lessons/quizzes/{question_id}', 'DELETE', ['api', 'auth'])]
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
