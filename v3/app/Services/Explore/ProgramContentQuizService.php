<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CohortLessonQuiz;

class ProgramContentQuizService
{
    private CohortLessonQuiz $cohortLessonQuizModel;

    public function __construct(\PDO $pdo)
    {
        $this->cohortLessonQuizModel = new CohortLessonQuiz($pdo);
    }

    public function getLessonQuiz(int $lessonId): array
    {
        $rows = $this->cohortLessonQuizModel
            ->select([
                'question_id',
                'title AS question',
                'type AS question_type',
                'answer',
                'correct'
            ])
            ->where('lesson_id', $lessonId)
            ->get();

        return array_map(fn($q) => [
            'id' => $q['question_id'],
            'question' => $q['question'],
            'type' => $q['question_type'],
            'options' => json_decode($q['answer'], true),
            'correct' => json_decode($q['correct'], true)
        ], $rows);
    }
}
