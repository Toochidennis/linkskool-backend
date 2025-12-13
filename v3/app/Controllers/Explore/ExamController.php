<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ExamService;

#[Group('/public')]
class ExamController extends ExploreBaseController
{
    private ExamService $examService;

    public function __construct()
    {
        parent::__construct();
        $this->examService = new ExamService($this->pdo);
    }

    #[Route('/questions', 'POST', ['api', 'auth'])]
    public function storeQuestions(): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'settings' => 'required|array|min:1',
                'settings.exam_type_id' => 'required|integer|min:1',
                'settings.course_id' => 'required|integer|min:1',
                'settings.course_name' => 'required|string|max:255',
                'settings.description' => 'nullable|string',
                'settings.user_id' => 'required|integer|min:1',
                'settings.username' => 'required|string|max:255',

                'file' => 'required|array|min:1',
                'file.name' => 'required|string|filled',
                'file.type' => 'required|string|filled|in:application/zip,application/x-zip-compressed,multipart/x-zip',
                'file.tmp_name' => 'required|string|filled',
            ]
        );

        $response = $this->examService->createQuestions($data);

        if (!$response['status']) {
            $this->respondError(
                "Failed to create exam questions. " . implode(', ', $response['errors']),
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Exam created successfully.',
            'exam_ids' => $response['exam_ids']
        ]);
    }

    #[Route('/questions', 'PUT', ['api', 'auth'])]
    public function updateQuestions(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'settings' => 'required|array|min:1',
            'settings.exam_id' => 'required|integer|min:1',
            'settings.exam_type_id' => 'required|integer|min:1',
            'settings.course_id' => 'required|integer|min:1',
            'settings.course_name' => 'required|string|max:255',
            'settings.description' => 'nullable|string',
            'settings.user_id' => 'required|integer|min:1',
            'settings.username' => 'required|string|max:255',
            'settings.year' => 'required|integer',

            'questions' => 'required|array|min:1',
            'questions.*.question_id' => 'sometimes|integer',
            'questions.*.question_text' => 'required|string|filled',
            'questions.*.instruction' => 'nullable|string',
            'questions.*.instruction_id' => 'nullable|integer',
            'questions.*.passage' => 'nullable|string',
            'questions.*.passage_id' => 'nullable|integer',
            'questions.*.topic' => 'nullable|string',
            'questions.*.topic_id' => 'nullable|integer',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.explanation_id' => 'nullable|integer',
            'questions.*.question_type' => 'required|string|in:short_answer,multiple_choice',

            'questions.*.question_files' => 'nullable|array',
            'questions.*.question_files.*.file_name' => 'required_with:questions.*.question_files.*.file|string|filled',
            'questions.*.question_files.*.old_file_name' => 'required_with:questions.*.question_files.*.file|string|filled',
            'questions.*.question_files.*.type' => 'required_with:questions.*.question_files|string|filled',
            'questions.*.question_files.*.file' => 'nullable|string',

            'questions.*.options' => 'required_if:questions.*.question_type,multiple_choice|array',
            'questions.*.options.*.order' => 'required_with:questions.*.options|integer',
            'questions.*.options.*.text' => 'nullable|string',

            'questions.*.options.*.option_files' => 'nullable|array',
            'questions.*.options.*.option_files.*.file_name' => 'required_with:questions.*.options.*.option_files.*.file|string|filled',
            'questions.*.options.*.option_files.*.old_file_name' => 'required_with:questions.*.options.*.option_files.*.file|string|filled',
            'questions.*.options.*.option_files.*.type' => 'required_with:questions.*.options.*.option_files|string|filled',
            'questions.*.options.*.option_files.*.file' => 'nullable|string',

            'questions.*.correct' => 'required|array',
            'questions.*.correct.order' => 'required|integer',
            'questions.*.correct.text' => 'required|string|filled',
        ]);

        $updated = $this->examService->updateQuestions($data);

        if (!$updated) {
            $this->respondError(
                'Failed to update exam questions.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Exam questions updated successfully.'
        ]);
    }

    #[Route('/exams', 'GET', ['api', 'auth'])]
    public function getExams(array $vars): void
    {
        $filters = $this->validate($vars, [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $this->respond([
            'success' => true,
            'data' => $this->examService->getExams($filters)
        ]);
    }

    #[Route('/exams/{exam_id:\d+}/questions', 'GET', ['api', 'auth'])]
    public function getQuestions(array $vars): void
    {
        $data = $this->validate($vars, [
            'exam_id' => 'required|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:0'
        ]);

        $questions = $this->examService->getQuestions($data);

        $this->respond([
            'success' => true,
            'data' => $questions
        ]);
    }

    #[Route('/exams', 'DELETE', ['api', 'auth'])]
    public function deleteExam(array $vars): void
    {
        $data = $this->validate($this->getRequestData(), [
            'exam_id' => 'required|integer|min:1',
            'user_id' => 'required|integer|min:1',
            'username' => 'required|string|filled'
        ]);

        $deleted = $this->examService->deleteExam($data);

        if (!$deleted) {
            $this->respondError(
                'Failed to delete exam.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Exam deleted successfully.'
        ]);
    }
}
