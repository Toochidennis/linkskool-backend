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

    #[Route('/questions', 'POST', ['api'])]
    public function storeQuestions(): void
    {
        $data = $this->validate([...$this->post['data'], ...$this->post['files']], [
            'settings' => 'required|array|min:1',
            'settings.exam_type_id' => 'required|integer|min:1',
            'settings.course_id' => 'required|integer|min:1',
            'settings.course_name' => 'required|string|max:255',
            'settings.description' => 'nullable|string',
            'settings.user_id' => 'required|integer|min:1',
            'settings.username' => 'required|string|max:255',

            'file' => 'required|array|min:1',
            'file.name' => 'required|string|filled',
            'file.type' => 'required|string|filled|in:application/zip',
            'file.tmp_name' => 'required|string|filled',
        ]);

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

    public function updateQuestions(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'settings' => 'required|array|min:1',
            'settings.exam_type_id' => 'required|integer|min:1',
            'settings.course_id' => 'required|integer|min:1',
            'settings.course_name' => 'required|string|max:255',
            'settings.description' => 'nullable|string',
            'settings.user_id' => 'required|integer|min:1',
            'settings.username' => 'required|string|max:255',

            'data' => 'required|array|min:1',

            'data.*.year' => 'required|integer|min:1',
            'data.*.questions' => 'required|array|min:1',

            'data.*.questions.*.question_text' => 'required|string|filled',
            'data.*.questions.*.instruction' => 'nullable|string',
            'data.*.questions.*.instruction_id' => 'nullable|integer',
            'data.*.questions.*.passage' => 'nullable|string',
            'data.*.questions.*.passage_id' => 'nullable|integer',
            'data.*.questions.*.topic' => 'nullable|string',
            'data.*.questions.*.topic_id' => 'nullable|integer',
            'data.*.questions.*.explanation' => 'nullable|string',
            'data.*.questions.*.explanation_id' => 'nullable|integer',
            'data.*.questions.*.question_type' => 'required|string|in:short_answer,multiple_choice',

            'data.*.questions.*.question_files' => 'nullable|array',
            'data.*.questions.*.question_files.*.file_name' => 'required_with:data.*.questions.*.question_files|string|filled',
            'data.*.questions.*.question_files.*.old_file_name' => 'nullable|string',
            'data.*.questions.*.question_files.*.type' => 'required_with:data.*.questions.*.question_files|string|filled',
            'data.*.questions.*.question_files.*.file' => 'required_with:data.*.questions.*.question_files|string|filled',

            'data.*.questions.*.options' => 'required_if:data.*.questions.*.question_type,multiple_choice|array',
            'data.*.questions.*.options.*.order' => 'required_with:data.*.questions.*.options|integer',
            'data.*.questions.*.options.*.text' => 'nullable|string',

            'data.*.questions.*.options.*.option_files' => 'nullable|array',
            'data.*.questions.*.options.*.option_files.*.file_name' => 'required_with:data.*.questions.*.options.*.option_files|string|filled',
            'data.*.questions.*.options.*.option_files.*.old_file_name' => 'nullable|string',
            'data.*.questions.*.options.*.option_files.*.type' => 'required_with:data.*.questions.*.options.*.option_files|string|filled',
            'data.*.questions.*.options.*.option_files.*.file' => 'required_with:data.*.questions.*.options.*.option_files|string|filled',

            'data.*.questions.*.correct' => 'required|array',
            'data.*.questions.*.correct.order' => 'required|integer',
            'data.*.questions.*.correct.text' => 'required|string|filled',
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

    #[Route('/exams/questions', 'GET', ['api', 'auth'])]
    public function getQuestions(array $vars): void
    {
        $data = $this->validate($vars, [
            'exam_id' => 'required|integer|min:1',
        ]);

        $questions = $this->examService->getQuestions($data['exam_id']);

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
