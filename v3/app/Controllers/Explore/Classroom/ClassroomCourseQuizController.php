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

    #[Route('/{course_id}/quizzes/questions', 'POST', ['api'])]
    public function create(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'question_id'      => 'nullable|integer',
                'quiz_settings_id' => 'required|integer',
                'course_id'        => 'required|integer',
                'question_text'    => 'required|string',
                'options'          => 'required|array',
                'options.*.text'   => 'required|string',
                'correct'          => 'required|array',
                'correct.text'     => 'required|string',
                'correct.order'    => 'required|integer',
            ]
        );

        $result = $this->service->create($validated);

        if (!$result) {
            $this->respondError('Failed to save quiz question.', HttpStatus::BAD_REQUEST);
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

    #[Route('/{course_id}/quizzes/questions/bulk', 'POST', ['api'])]
    public function createMany(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'course_id'              => 'required|integer',
                'quiz_settings_id'       => 'required|integer',
                'questions'              => 'required|array|min:1',
                'questions.*.question_text' => 'required|string',
                'questions.*.options'       => 'required|array',
                'questions.*.options.*.text' => 'required|string',
                'questions.*.correct'       => 'required|array',
                'questions.*.correct.text'  => 'required|string',
                'questions.*.correct.order' => 'required|integer',
            ]
        );

        $count = $this->service->createMany(
            (int) $validated['quiz_settings_id'],
            (int) $validated['course_id'],
            $validated['questions']
        );

        if (!$count) {
            $this->respondError('Failed to save questions.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status'  => true,
                'message' => "{$count} question(s) saved successfully.",
                'data'    => ['count' => $count],
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{course_id}/quizzes/questions/{question_id}', 'PUT', ['api'])]
    public function update(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'question_id'      => 'required|integer',
                'quiz_settings_id' => 'required|integer',
                'course_id'        => 'required|integer',
                'question_text'    => 'required|string',
                'options'          => 'required|array',
                'options.*.text'   => 'required|string',
                'correct'          => 'required|array',
                'correct.text'     => 'required|string',
                'correct.order'    => 'required|integer',
            ]
        );

        $result = $this->service->update($validated);

        if (!$result) {
            $this->respondError('Failed to update quiz question.', HttpStatus::BAD_REQUEST);
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

    #[Route('/{course_id}/quizzes/questions/generate', 'POST', ['api'])]
    public function generateQuestions(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'count'        => 'required|integer',
                'course_name'  => 'required|string',
                'subject_name' => 'nullable|string',
                'level_name'   => 'nullable|string',
                'topic'        => 'nullable|string',
            ]
        );

        $questions = $this->service->generateQuestions($validated);

        $this->respond(
            [
                'status' => true,
                'data'   => $questions,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{course_id}/quizzes/settings', 'POST', ['api'])]
    public function saveSettings(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'course_id'      => 'required|integer',
                'institution_id' => 'required|integer',
                'lesson_id'      => 'nullable|integer',
                'topic'          => 'nullable|string',
                'duration'       => 'nullable|integer',
                'start_date'     => 'nullable|date',
                'end_date'       => 'nullable|date|after_or_equal:start_date',
            ]
        );

        $settings = $this->service->saveSettings($validated);

        if (!$settings) {
            $this->respondError('Failed to save quiz settings.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Quiz settings saved successfully.',
                'data'    => $settings,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/quizzes/settings/{settings_id}', 'GET', ['api'])]
    public function getSettings(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'settings_id' => 'required|integer',
            ]
        );

        $settings = $this->service->getSettings((int) $validated['settings_id']);

        $this->respond(
            [
                'status' => true,
                'data'   => $settings,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{course_id}/quizzes/questions', 'GET', ['api'])]
    public function getQuestions(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'course_id'        => 'required|integer',
                'quiz_settings_id' => 'required|integer',
            ]
        );

        $questions = $this->service->getQuestions((int) $validated['quiz_settings_id']);

        $this->respond(
            [
                'status' => true,
                'data'   => $questions,
            ],
            HttpStatus::OK
        );
    }
}
