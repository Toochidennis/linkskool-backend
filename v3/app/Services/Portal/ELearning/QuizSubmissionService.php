<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Common\Enums\SubmissionStatus;
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
            'unmarked' => SubmissionStatus::UNMARKED->value,
            'level' => $data['level_id'],
            'course_id' => $data['course_id'],
            'class' => $data['class_id'],
            'course_name' => $data['course_name'],
            'class_name' => $data['class_name'],
            'type' => ContentType::QUIZ->value,
            'term' => $data['term'],
            'year' => $data['year'],
            'date' => date('Y-m-d H:i:s')
        ];

        $exists = $this->submission
            ->where('exam', $data['quiz_id'])
            ->where('student', $data['student_id'])
            ->where('type', ContentType::QUIZ->value)
            ->where('year', $data['year'])
            ->where('term', $data['term'])
            ->exists();

        if (!$exists) {
            return $this->submission->insert($payload);
        }

        return true;
    }

    public function markQuiz(array $data): bool
    {
        $payload = [
            'unmarked' => SubmissionStatus::MARKED->value,
            'marking' => SubmissionStatus::MARKED->value,
            'score' => $data['score'],
        ];

        return $this->submission
            ->where('response_id', $data['id'])
            ->update($payload);
    }

    public function publishQuiz(array $data): bool
    {
        return $this->submission
            ->where('exam', $data['content_id'])
            ->where('year', $data['year'])
            ->where('term', $data['term'])
            ->update(['marking' => $data['publish']]);
    }

    public function getQuizSubmissions(array $filters): array
    {
        $results =  $this->submission
            ->select([
                'response_id AS id',
                'exam AS content_id',
                'student AS student_id',
                'student_name',
                'response AS answers',
                'marking_score',
                'unmarked',
                'score',
                'date'
            ])
            ->where('exam', $filters['id'])
            ->where('type', ContentType::QUIZ->value)
            ->where('year', $filters['year'])
            ->where('term', $filters['term'])
            ->orderBy('date', 'DESC')
            ->get();

        $grouped = [
            'submitted' => [],
            'unmarked' => [],
            'marked' => []
        ];

        foreach ($results as $row) {
            $row['answers'] = json_decode($row['answers'], true);

            $status = $row['unmarked'];
            unset($row['unmarked']);

            $grouped['submitted'][] = $row;

            if ($status == SubmissionStatus::UNMARKED->value) {
                $grouped['unmarked'][] = $row;
            }
            if ($status == SubmissionStatus::MARKED->value) {
                $grouped['marked'][] = $row;
            }
        }

        return $grouped;
    }

    public function getMarkedQuiz(array $filters): array
    {
        $result = $this->submission
            ->select([
                'student_name',
                'response AS answers',
                'marking_score',
                'score',
                'date'
            ])
            ->where('exam', $filters['id'])
            ->where('student', $filters['student_id'])
            ->where('type', ContentType::QUIZ->value)
            ->where('year', $filters['year'])
            ->where('term', $filters['term'])
            ->where('marking', SubmissionStatus::MARKED->value)
            ->orderBy('date', 'DESC')
            ->first();

        if ($result) {
            $result['answers'] = json_decode($result['answers'], true);
        }

        return $result;
    }
}
