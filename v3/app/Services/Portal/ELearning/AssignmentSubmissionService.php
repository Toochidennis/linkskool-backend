<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Common\Enums\SubmissionStatus;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\ELearning\Submission;

class AssignmentSubmissionService
{
    private Submission $submission;
    private FileHandler $fileHandler;

    public function __construct(\PDO $pdo)
    {
        $this->submission = new Submission($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function submitAssignment(array $data): bool
    {
        $filePath = $this->fileHandler->handleFiles($data['files']);

        $payload = [
            'exam' => $data['assignment_id'],
            'student' => $data['student_id'],
            'student_name' => $data['student_name'],
            'response' => json_encode($filePath),
            'marking_score' => $data['mark'],
            'unmarked' => SubmissionStatus::UNMARKED->value,
            'score' => $data['score'],
            'level' => $data['level_id'],
            'course_id' => $data['course_id'],
            'class' => $data['class_id'],
            'course_name' => $data['course_name'],
            'class_name' => $data['class_name'],
            'type' => ContentType::ASSIGNMENT->value,
            'term' => $data['term'],
            'year' => $data['year'],
            'date' => date('Y-m-d H:i:s')
        ];

        $exists = $this->submission
            ->where('exam', $data['assignment_id'])
            ->where('student', $data['student_id'])
            ->where('type', ContentType::ASSIGNMENT->value)
            ->where('year', $data['year'])
            ->where('term', $data['term'])
            ->exists();

        if (!$exists) {
            return $this->submission->insert($payload);
        }

        return true;
    }

    public function markAssignment(array $data): bool
    {
        foreach ($data as $row) {
            $payload = [
                'response' => json_encode($row['answers']),
                'unmarked' => SubmissionStatus::MARKED->value,
                'marking' => SubmissionStatus::PUBLISHED->value,
                'score' => $row['score'],
            ];

            $this->submission
                ->where('response_id', $row['id'])
                ->update($payload);
        }

        return true;
    }

    public function publishAssignment(array $data): bool
    {
        foreach ($data as $row) {
            $this->submission
                ->where('response_id', $row['id'])
                ->update(['marking' => $row['publish']]);
        }

        return true;
    }

    public function getAssignmentSubmissions(array $filters): array
    {
        $results =  $this->submission
            ->select([
                'response_id AS id',
                'exam AS content_id',
                'student AS student_id',
                'student_name',
                'response AS files',
                'marking_score',
                'unmarked',
                'score',
                'date'
            ])
            ->where('exam', $filters['id'])
            ->where('type', ContentType::ASSIGNMENT->value)
            ->where('year', $filters['year'])
            ->where('term', $filters['term'])
            ->orderBy('date', 'DESC')
            ->get();

        $grouped = [
            'submitted' => [],
            'unmarked'  => [],
            'marked'    => []
        ];

        foreach ($results as $row) {
            $row['files'] = json_decode($row['files'], true);

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

    public function getMarkedAssignment(array $filters): array
    {
        $result = $this->submission
            ->select([
                'response AS files',
                'marking_score',
                'score',
                'date'
            ])
            ->where('exam', $filters['content_id'])
            ->where('type', ContentType::ASSIGNMENT->value)
            ->where('year', $filters['year'])
            ->where('term', $filters['term'])
            ->where('marking', SubmissionStatus::PUBLISHED->value)
            ->orderBy('date', 'DESC')
            ->first();

        if ($result) {
            $result['files'] = json_decode($result['files'], true);
        }

        return $result;
    }
}
