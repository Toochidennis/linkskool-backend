<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\QuizService;

class QuizController extends BaseController
{
    private QuizService $quizService;

    public function __construct()
    {
        parent::__construct();
        $this->quizService = new QuizService($this->pdo);
    }

    public function store()
    {
        $data =  $this->validate(
            data: $this->post,
            rules: [
                'settings' => 'required|array',
                'settings.*.title' => 'required|string|filled',
                'settings.*.description' => 'required|string|filled',
                'settings.*.topic' => 'required|string|filled',
                'settings.*.topic_id' => 'required|integer',
                'settings.*.syllabus_id' => 'required|integer',
                'settings.*.duration' => 'required|integer',
                'settings.*.start_date' => 'required|date|filled',
                'settings.*.end_date' => 'required|date|filled',
                'settings.*.creator_name' => 'required|string|filled',
                'settings.*.creator_id' => 'required|integer',
                'settings.*.classes' => 'required|array|min:1',
                'settings.*.classes.*.id' => 'required|integer',
                'settings.*.classes.*.name' => 'required|string|filled',

                'questions' => 'required|array|min:1',
                'questions.*.question_text' => 'required|string|filled',
                'questions.*.question_grade' => 'required|numeric',
                'questions.*.question_type' => 'required|string|in:short_answer,multiple_choice',

                'questions.*.question_files' => 'required|array|min:1',
                'questions.*.question_files.*.file_name' => 'required|string|filled',
                'questions.*.question_files.*.old_file_name' => 'required|string|filled',
                'questions.*.question_files.*.type' => 'required|string|filled',
                'questions.*.question_files.*.file' => 'required|string|filled',

                'questions.*.options' => 'nullable|array|min:1',
                'questions.*.options.*.order' => 'required|integer',
                'questions.*.options.*.text' => 'required|string',

                'questions.*.options.*.option_files' => 'required|array',
                'questions.*.options.*.option_files.file_name' => 'required|string|filled',
                'questions.*.options.*.option_files.old_file_name' => 'required|string',
                'questions.*.options.*.option_files.type' => 'required|string|filled',
                'questions.*.options.*.option_files.file' => 'required|string|filled',

                'questions.*.correct' => 'required|array',
                'questions.*.correct.order' => 'required|integer',
                'questions.*.correct.text' => 'required|string|filled',
            ]
        );

        try {
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
