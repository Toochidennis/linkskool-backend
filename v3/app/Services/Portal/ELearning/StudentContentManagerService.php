<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Models\Portal\ELearning\Content;
use V3\App\Models\Portal\Results\Result;

class StudentContentManagerService
{
    private Content $content;
    private Result $result;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->result = new Result($pdo);
    }

    private function getCourses(array $filters): array
    {
        $courses = [];

        $syllabi = $this->content
            ->select(columns: ['id', 'title', 'path_label', 'course_id', 'course_name', 'level'])
            ->where('term', '=', $filters['term'])
            ->where('level', '=', $filters['level_id'])
            ->where('type', '=', ContentType::SYLLABUS->value)
            ->orderBy('course_name')
            ->get();

        if (empty($syllabi)) {
            return [];
        }

        $seenCourseIds = [];

        foreach ($syllabi as $syllabus) {
            $courseId = $syllabus['course_id'];

            if (in_array($courseId, $seenCourseIds, true)) {
                continue; // Skip duplicate course
            }

            if (
                $this->hasClass(
                    $this->json($syllabus['path_label']),
                    $filters['class_id']
                )
            ) {
                $isRegistered = $this->result
                    ->where('year', '=', $filters['year'])
                    ->where('term', '=', $filters['term'])
                    ->where('reg_no', '=', $filters['id'])
                    ->where('course', '=', $courseId)
                    ->where('class', '=', $filters['class_id'])
                    ->exists();

                if ($isRegistered) {
                    $courses[] = [
                        'syllabus_id' => $syllabus['id'],
                        'course_id' => $courseId,
                        'level_id' => $syllabus['level'],
                        'title' => $syllabus['title'],
                        'course_name' => $syllabus['course_name'],
                    ];
                    $seenCourseIds[] = $courseId; // Mark as seen
                }
            }
        }

        return $courses;
    }

    private function getRecentQuiz(array $filters)
    {
        $quizzes = [];
        $results = $this->content
            ->select(columns: [
                'id',
                'outline',
                'title',
                'course_name',
                'course_id',
                'level',
                'path_label',
                'upload_date',
                'author_name'
            ])
            ->where('term', '=', $filters['term'])
            ->where('level', '=', $filters['level_id'])
            ->where('type', '=', ContentType::QUIZ->value)
            ->orderBy('upload_date', 'DESC')
            ->limit(10)
            ->get();

        if (empty($results)) {
            return [];
        }

        foreach ($results as $result) {
            if (
                $this->hasClass(
                    $this->json($result['path_label']),
                    $filters['class_id']
                )
            ) {
                if (!empty($result['outline'])) {
                    $quizzes[] = [
                        'id' => $result['id'],
                        'syllabus_id' => $result['outline'],
                        'course_id' => $result['course_id'],
                        'title' => $result['title'],
                        'course_name' => $result['course_name'],
                        'level_id' => $result['level'],
                        'created_by' => $result['author_name'],
                        'date_posted' => $result['upload_date'],
                    ];
                }
            }
        }

        return $quizzes;
    }

    private function getRecentActivities(array $filters)
    {
        $activities = [];
        $results = $this->content
            ->select(columns: [
                'id',
                'outline',
                'title',
                'type',
                'course_name',
                'course_id',
                'level',
                'body',
                'path_label',
                'author_name',
                'upload_date'
            ])
            ->where('term', '=', $filters['term'])
            ->where('level', '=', $filters['level_id'])
            ->orderBy('upload_date', 'DESC')
            ->limit(10)
            ->get();

        if (empty($results)) {
            return [];
        }

        foreach ($results as $result) {
            if (
                $this->hasClass(
                    $this->json($result['path_label']),
                    $filters['class_id']
                )
            ) {
                if (!empty($result['outline'])) {
                    $activities[] = [
                        'id' => $result['id'],
                        'syllabus_id' => $result['outline'],
                        'course_id' => $result['course_id'],
                        'level_id' => $result['level'],
                        'title' => $result['title'],
                        'comment' => $result['body'],
                        'type' => ContentType::tryFrom($result['type'])?->label() ?? 'Unknown',
                        'course_name' => $result['course_name'],
                        'created_by' => $result['author_name'],
                        'date_posted' => $result['upload_date'],
                    ];
                }
            }
        }

        return $activities;
    }

    public function getStudentDashboardData(array $filters): array
    {
        return [
            'recent_quizzes' => $this->getRecentQuiz($filters),
            'recent_activities' => $this->getRecentActivities($filters),
            'available_courses' => $this->getCourses($filters),
        ];
    }

    private function json(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function hasClass(array $pathLabel, string|int $targetClassId): bool
    {
        return !empty(array_filter($pathLabel, fn($cls) => $cls['id'] == $targetClassId));
    }
}
