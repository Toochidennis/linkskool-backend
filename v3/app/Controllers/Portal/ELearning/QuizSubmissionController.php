<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\QuizSubmissionService;

class QuizSubmissionController extends BaseController
{
    private QuizSubmissionService $quizSubmissionService;

    public function __construct()
    {
        parent::__construct();
        $this->quizSubmissionService = new QuizSubmissionService($this->pdo);
    }

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

        try {
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
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to submit quiz: ' . $e->getMessage()
            );
        }
    }

    public function markQuizSubmission(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'results' => 'required|array|min:1',
                'results.*.id' => 'required|integer',
                'results.*.answers' => 'required|array',
                'results.*.score' => 'required|numeric',
            ]
        );

        try {
            $result = $this->quizSubmissionService->markQuiz($cleanedData['results']);

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
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to mark quiz: ' . $e->getMessage()
            );
        }
    }

    public function publishQuiz(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
                'publish' => 'required|integer|in:0,1',
            ]
        );

        try {
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
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to publish quiz: ' . $e->getMessage()
            );
        }
    }

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

        try {
            $submissions = $this->quizSubmissionService->getQuizSubmissions($cleanedData);

            return $this->respond(
                [
                    'success' => true,
                    'data' => $submissions
                ],
            );
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to retrieve quiz submissions: ' . $e->getMessage()
            );
        }
    }

    public function getMarkedQuiz(array $vars)
    {
        $filteredVars = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
                'student_id' => 'required|integer',
                'term' => 'required|integer|in:1,2,3',
                'year' => 'required|integer',
            ]
        );

        try {
            $markedQuiz = $this->quizSubmissionService->getMarkedQuiz($filteredVars);

            return $this->respond(
                [
                    'success' => true,
                    'response' => $markedQuiz,
                ]
            );
        } catch (\Exception $e) {
            return $this->respondError(
                'Failed to retrieve quiz submissions: ' . $e->getMessage()
            );
        }
    }
}
