<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ExamService;

#[Group('/public/cbt')]
class ExamController extends ExploreBaseController
{
    private ExamService $examService;

    public function __construct()
    {
        parent::__construct();
        $this->examService = new ExamService($this->pdo);
    }

    #[Route('/exams', 'POST', ['api'])]
    public function storeExam(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'settings' => 'required|array|min:1',
            'settings.exam_type_id' => 'required|integer|min:1',
            'settings.course_id' => 'required|integer|min:1',
            'settings.course_name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'data' => 'required|array|min:1',

            'data.*.year' => 'required|integer|min:1900|max: ' . (date('Y') + 1),
            'data.*.questions' => 'required|array|min:1',

            'data.*.questions.*.question_text' => 'required|string|filled',
            'data.*.questions.*.instruction' => 'nullable|string',
            'data.*.questions.*.instruction_id' => 'nullable|integer|min:1',
            'data.*.questions.*.passage' => 'nullable|string',
            'data.*.questions.*.passage_id' => 'nullable|integer|min:1',
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

        $examId = $this->examService->createExam($data);

        if ($examId <= 0) {
            $this->respondError(
                'Failed to create exam.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Exam created successfully.',
            'exam_id' => $examId
        ]);
    }
}
