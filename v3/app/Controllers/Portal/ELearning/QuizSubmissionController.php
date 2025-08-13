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

    public function markQuiz(array $vars)
    {
        //TODO
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
