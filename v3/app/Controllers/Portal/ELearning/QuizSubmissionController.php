<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\ELearning\QuizSubmissionService;

#[Group('/portal')]
class QuizSubmissionController extends BaseController
{
    private QuizSubmissionService $quizSubmissionService;

    public function __construct()
    {
        parent::__construct();
        $this->quizSubmissionService = new QuizSubmissionService($this->pdo);
    }

    #[Route(
        '/students/{student_id:\d+}/quiz-submissions',
        'POST',
        ['auth', 'role:student']
    )]
    public function submit(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'quiz_id' => 'required|integer',
                'student_id' => 'required|integer',
                'student_name' => 'required|string|filled',
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer',
                'answers.*.question' => 'required|string|filled',
                'answers.*.correct' => 'required|string|filled',
                'answers.*.answer' => 'required|string|filled',
                'answers.*.type' => 'required|string|in:multiple_choice,short_answer',
                'mark' => 'required|numeric',
                'score' => 'required|numeric',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer',
                'class_id' => 'required|integer',
                'course_name' => 'required|string|filled',
                'class_name' => 'required|string|filled',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer'
            ]
        );

        $newId = $this->quizSubmissionService->submitQuiz($cleanedData);

        if ($newId) {
            return $this->respond(
                [
                    'success' => true,
                    'message' => 'Quiz submitted successfully',
                ],
                HttpStatus::CREATED
            );
        } else {
            return $this->respondError(
                'Failed to submit quiz',
                HttpStatus::BAD_REQUEST
            );
        }
    }

    #[Route('/elearning/quiz/mark', 'PUT', ['auth', 'role:admin', 'role:staff'])]
    public function markQuiz(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'score' => 'required|numeric',
            ]
        );

        $result = $this->quizSubmissionService->markQuiz($cleanedData);

        if ($result) {
            return $this->respond(
                [
                    'success' => true,
                    'message' => 'Quiz marked successfully',
                ],
                HttpStatus::OK
            );
        }

        return $this->respondError(
            'Failed to mark quiz',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/elearning/quiz/{content_id:\d+}/publish',
        'PUT',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function publishQuiz(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'content_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'publish' => 'required|integer|in:0,1',
            ]
        );

        $result = $this->quizSubmissionService->publishQuiz($cleanedData);

        if (!$result) {
            return $this->respondError(
                'Failed to publish quiz',
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->respond(
            [
                'success' => true,
                'message' => 'Quiz published successfully',
            ]
        );
    }

    #[Route(
        '/elearning/quiz/{id:\d+}/submissions',
        'GET',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function getSubmissions(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer'
            ]
        );

        $submissions = $this->quizSubmissionService->getQuizSubmissions($cleanedData);

        return $this->respond(
            [
                'success' => true,
                'data' => $submissions
            ],
        );
    }

    #[Route(
        '/students/{student_id:\d+}/quiz-submissions',
        'GET',
        ['auth', 'role:student']
    )]
    public function getMarkedQuiz(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: [
                'content_id' => 'required|integer',
                'student_id' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer',
            ]
        );

        $markedQuiz = $this->quizSubmissionService->getMarkedQuiz($filteredVars);

        return $this->respond(
            [
                'success' => true,
                'response' => $markedQuiz,
            ]
        );
    }
}
