<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Models\Portal\ELearning\Submission;

class QuizSubmissionService
{
    private Submission $submission;

    public function __construct(\PDO $pdo)
    {
        $this->submission = new Submission($pdo);
    }

    public function submitQuiz(array $data): bool
    {
        $payload = [
            'exam' => $data['quiz_id'],
            'student' => $data['student_id'],
            'student_name' => $data['student_name'],
            'response' => json_encode($data['answers']),
            'marking_score' => $data['mark'],
            'score' => $data['score'],
            'level' => $data['level_id'],
            'course' => $data['course_id'],
            'class_id' => $data['class_id'],
            'course_name' => $data['course_name'],
            'class_name' => $data['class_name'],
            'type' => ContentType::QUIZ->value,
            'term' => $data['term'],
            'year' => $data['year'],
            'date' => date('Y-m-d H:i:s')
        ];

        return $this->submission->insert($payload);
    }

    public function getQuizSubmissions(array $filters): array
    {
        $results =  $this->submission
            ->select([
                'id',
                'exam AS content_id',
                'student AS student_id',
                'student_name',
                'response AS answers',
                'marking_score',
                'unmarked',
                'marking',
                'score',
                'date'
            ])
            ->where('exam', $filters['content_id'])
            ->where('type', ContentType::QUIZ->value)
            ->where('year', $filters['year'])
            ->orderBy('date', 'DESC')
            ->get();

        $grouped = [
            'submitted' => [],
            'marked' => [],
            'returned' => []
        ];

        foreach ($results as $row) {
            $row['response'] = json_decode($row['answers'], true);

            if (empty($row['unmarked']) || $row['unmarked'] == 0) {
                $grouped['submitted'][] = $row;
            }
            if ($row['unmarked'] == 1) {
                $grouped['marked'][] = $row;
            }
            if ($row['marking'] == 1) {
                $grouped['returned'][] = $row;
            }
        }

        return $grouped;
    }
}
