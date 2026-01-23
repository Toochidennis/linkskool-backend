<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CohortLessonQuiz;

class CohortLessonQuizService
{
    private CohortLessonQuiz $cohortLessonQuizModel;

    public function __construct(\PDO $pdo)
    {
        $this->cohortLessonQuizModel = new CohortLessonQuiz($pdo);
    }

    public function create(array $data)
    {
        $payload = [
            'lesson_id' => $data['lesson_id'],
            'program_id' => $data['program_id'],
            'course_id' => $data['course_id'],
            'cohort_id' => $data['cohort_id'],
            'title' => $data['question_text'],
            'answer' => json_encode($data['options']),
            'correct' => json_encode($data['correct']),
        ];

        if (isset($data['question_id']) && !empty($data['question_id']) && $data['question_id'] > 0) {
            return $this->cohortLessonQuizModel
                ->where('question_id', '=', $data['question_id'])
                ->update($payload);
        }

        return $this->cohortLessonQuizModel->insert($payload);
    }

    public function update(array $data)
    {
        $payload = [
            'lesson_id' => $data['lesson_id'],
            'program_id' => $data['program_id'],
            'course_id' => $data['course_id'],
            'cohort_id' => $data['cohort_id'],
            'title' => $data['question_text'],
            'answer' => json_encode($data['options']),
            'correct' => json_encode($data['correct']),
        ];

        return $this->cohortLessonQuizModel
            ->where('question_id', '=', $data['question_id'])
            ->update($payload);
    }

    public function getQuizByLessonId(int $lessonId): array
    {
        $quizzes = $this->cohortLessonQuizModel
            ->select([
                'question_id',
                'title AS question_text',
                'answer AS options',
                'correct',
            ])
            ->where('lesson_id', $lessonId)
            ->get();

        return array_map(fn($q) => [
            'question_id' => $q['question_id'],
            'question_text' => $q['question_text'],
            'options' => json_decode($q['options'], true),
            'correct' => json_decode($q['correct'], true),
        ], $quizzes);
    }

    public function delete(int $questionId)
    {
        return $this->cohortLessonQuizModel
            ->where('question_id', '=', $questionId)
            ->delete();
    }
}
