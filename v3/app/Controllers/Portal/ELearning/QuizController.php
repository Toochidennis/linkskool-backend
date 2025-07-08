<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
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
                'setting' => 'required|array',
                'setting.title' => 'required|string|filled',
                'setting.description' => 'required|string|filled',
                'setting.topic' => 'required|string|filled',
                'setting.topic_id' => 'required|integer',
                'setting.syllabus_id' => 'required|integer',
                'setting.duration' => 'required|integer',
                'setting.start_date' => 'required|date|filled',
                'setting.end_date' => 'required|date|filled',
                'setting.creator_name' => 'required|string|filled',
                'setting.creator_id' => 'required|integer',
                'setting.classes' => 'required|array|min:1',
                'setting.classes.*.id' => 'required|integer',
                'setting.classes.*.name' => 'required|string|filled',
                'setting.course_id' => 'required|integer',
                'setting.course_name' => 'required|string|filled',
                'setting.term' => 'required|integer',
                'setting.level_id' => 'required|integer',

                'questions' => 'required|array|min:1',
                'questions.*.question_text' => 'required|string|filled',
                'questions.*.question_grade' => 'required|numeric',
                'questions.*.question_type' => 'required|string|in:short_answer,multiple_choice',

                'questions.*.question_files' => 'sometimes|array|min:1',
                'questions.*.question_files.*.file_name' => 'required|string|filled',
                'questions.*.question_files.*.old_file_name' => 'sometimes|string',
                'questions.*.question_files.*.type' => 'required|string|filled',
                'questions.*.question_files.*.file' => 'required|string|filled',

                'questions.*.options' => 'sometimes|array|min:1',
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
            $contentId = $this->quizService->addQuiz($data);

            if ($contentId > 0) {
                $this->respond(
                    [
                        'success' => true,
                        'message' => 'Quiz added successfully',
                        'id' => $contentId
                    ],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to add quiz');
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
