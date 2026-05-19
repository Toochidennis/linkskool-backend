<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Classroom\ClassroomCourseQuiz;

class ClassroomCourseQuizService
{
    protected ClassroomCourseQuiz $model;

    public function __construct(\PDO $pdo)
    {
        $this->model = new ClassroomCourseQuiz($pdo);
    }

    public function create(array $data)
    {
        $payload = [
            'institution_id' => $data['institution_id'],
            'course_id' => $data['course_id'],
            'question_text' => $data['question_text'],
            'options' => json_encode($data['options']),
            'correct' => json_encode($data['correct']),
        ];

        if (isset($data['question_id']) && !empty($data['question_id']) && $data['question_id'] > 0) {
            return $this->model
                ->where('question_id', '=', $data['question_id'])
                ->update($payload);
        }

        return $this->model->insert($payload);
    }
    public function update(array $data)
    {
        $payload = [
            'institution_id' => $data['institution_id'],
            'course_id' => $data['course_id'],
            'question_text' => $data['question_text'],
            'options' => json_encode($data['options']),
            'correct' => json_encode($data['correct']),
        ];

        return $this->model
            ->where('question_id', '=', $data['question_id'])
            ->update($payload);
    }
}
