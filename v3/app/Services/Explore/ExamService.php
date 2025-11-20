<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Exam;

class ExamService
{
    private Exam $exam;

    public function __construct(\PDO $pdo)
    {
        $this->exam = new Exam($pdo);
    }

    public function createExam(array $data): int
    {
        return $this->exam->insert($data);
    }

    private function createQuestions(array $questions): void
    {
        foreach ($questions as $question) {
            $payload = [
                'exam_id' => $examId,
                'question_text' => $question['question_text'],
                'options' => json_encode($question['options']),
                'correct_option' => $question['correct_option'],
            ];
            $this->exam->insert($payload);
        }
    }
}
