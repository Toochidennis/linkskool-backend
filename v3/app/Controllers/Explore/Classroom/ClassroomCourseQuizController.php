<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomCourseQuizService;

#[Group('/public/classroom/courses')]
class ClassroomCourseQuizController extends ExploreBaseController
{
    private ClassroomCourseQuizService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomCourseQuizService($this->pdo);
    }

    #[Route('/{course_id}/quizzes', 'POST', ['api', 'auth'])]
    public function create(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'question_id'    => 'nullable|integer',
                'course_id'      => 'required|integer',
                'institution_id' => 'required|integer',
                'question_text'  => 'required|string',
                'options'        => 'required|array',
                'options.*.text' => 'required|string',
                'correct'        => 'required|array',
                'correct.text'   => 'required|string',
                'correct.order'  => 'required|integer',
            ]
        );

        $result = $this->service->create($validated);

        if (!$result) {
            $this->respondError(
                'Failed to save quiz question.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Quiz question saved successfully.',
                'data'    => ['question_id' => $result],
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{course_id}/quizzes/{question_id}', 'PUT', ['api', 'auth'])]
    public function update(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'question_id'    => 'required|integer',
                'course_id'      => 'required|integer',
                'institution_id' => 'required|integer',
                'question_text'  => 'required|string',
                'options'        => 'required|array',
                'options.*.text' => 'required|string',
                'correct'        => 'required|array',
                'correct.text'   => 'required|string',
                'correct.order'  => 'required|integer',
            ]
        );

        $result = $this->service->update($validated);

        if (!$result) {
            $this->respondError(
                'Failed to update quiz question.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Quiz question updated successfully.',
                'data'    => ['question_id' => $validated['question_id']],
            ],
            HttpStatus::OK
        );
    }
}
