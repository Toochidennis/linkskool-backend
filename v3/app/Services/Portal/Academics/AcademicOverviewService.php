<?php

namespace V3\App\Services\Portal\Academics;

use V3\App\Common\Enums\ContentType;
use V3\App\Models\Portal\Academics\ClassModel;
use V3\App\Models\Portal\Academics\Level;
use V3\App\Models\Portal\Academics\Staff;
use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\ELearning\Content;

class AcademicOverviewService
{
    private ClassModel $classModel;
    private Content $content;
    private Student $student;
    private Staff $staff;
    private Level $level;

    public function __construct(\PDO $pdo)
    {
        $this->classModel = new ClassModel($pdo);
        $this->content = new Content($pdo);
        $this->student = new Student($pdo);
        $this->staff = new Staff($pdo);
        $this->level = new Level($pdo);
    }

    private function getTotalStudents(?int $classId): int
    {
        if ($classId !== null) {
            return $this->student
                ->where('student_class', '=', $classId)
                ->where('student_level', '>', 0)
                ->count();
        } else {
            return $this->student
                ->where('student_level', '>', 0)
                ->count();
        }
    }

    private function getTotalTeachers(): int
    {
        return $this->staff
            ->where('status', 'not like', 'terminated%')
            ->count();
    }

    private function getTotalClasses(): int
    {
        return $this->classModel->count();
    }

    private function getTotalLevels(): int
    {
        return $this->level->count();
    }

    private function getFeeds(int $term): array
    {
        $contents =  $this->content
            ->select([
                'id',
                'title',
                'body AS content',
                'parent AS parent_id',
                'author_name',
                'author_id',
                'url as files',
                'type',
                'upload_date as created_at',
            ])
            ->in('type', [
                ContentType::NEWS->value,
                ContentType::QUESTION->value,
                ContentType::REPLY->value
            ])
            ->where('term', '=', $term)
            ->orderBy('upload_date', 'DESC')
            ->limit(15)
            ->get();

        $grouped = [
            'news' => [],
            'questions' => []
        ];

        // Index contents by ID for quick lookup
        $byId = [];
        foreach ($contents as $content) {
            $content['files'] = $this->json($content['files']);
            $content['replies'] = [];
            $byId[$content['id']] = $content;
        }

        // Build parent-child relationships
        foreach ($byId as $id => &$content) {
            if (!empty($content['parent_id']) && isset($byId[$content['parent_id']])) {
                $byId[$content['parent_id']]['replies'][] = &$content;
            }
        }
        unset($content); // break reference

        // Group into news and questions (ignore replies at top level)
        foreach ($byId as $id => $content) {
            if (!empty($content['parent_id']) && isset($byId[$content['parent_id']])) {
                continue; // already attached as a reply
            }

            if ($content['type'] === ContentType::NEWS->value) {
                $content['type'] =  ContentType::NEWS->label();
                $grouped['news'][] = $content;
            } elseif ($content['type'] === ContentType::QUESTION->value) {
                $content['type'] =  ContentType::QUESTION->label();
                $grouped['questions'][] = $content;
            }
        }

        return $grouped;
    }

    private function getStudentRecentQuiz(array $filters)
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

    private function hasClass(array $pathLabel, string|int $targetClassId): bool
    {
        return !empty(array_filter($pathLabel, fn($cls) => $cls['id'] == $targetClassId));
    }

    private function json(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function getRecentActivities(array $filters = []): array
    {
        $query = $this->content
            ->select([
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
            ->orderBy('upload_date', 'DESC');

        $results = $query->get();
        if (empty($results)) {
            return [];
        }

        $items = [];
        foreach ($results as $result) {
            if (
                $result['type'] == ContentType::NEWS->value ||
                $result['type'] == ContentType::QUESTION->value ||
                $result['type'] == ContentType::REPLY->value
            ) {
                continue;
            }

            $classes = $this->json($result['path_label']);
            $classIds = array_map(fn($item) => (int)$item['id'], $classes);
            $contentType = ContentType::tryFrom($result['type'])?->label() ?? 'Unknown';

            $item = [
                'id' => $result['id'],
                'syllabus_id' => $result['outline'],
                'course_id' => $result['course_id'],
                'level_id' => $result['level'],
                'title' => $result['title'],
                'course_name' => $result['course_name'],
                'classes' => $classes,
                'created_by' => $result['author_name'],
                'date_posted' => $result['upload_date'],
                'type' => $contentType,
                'comment' => $result['body'] ?? '',
            ];

            // staff filter
            if (!empty($filters)) {
                if (!in_array($result['course_id'], $filters['course_ids'] ?? [])) {
                    continue;
                }
                if (empty(array_intersect($classIds, $filters['class_ids'] ?? []))) {
                    continue;
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    private function getStaffAssignedCourses(array $filters): array
    {
        $rows = $this->classModel
            ->select([
                'class_table.id AS class_id',
                'class_table.class_name',
                'course_table.id AS course_id',
                'course_table.course_name',
                'class_table.level AS level_id',
            ])
            ->join('staff_course_table', 'class_table.id = staff_course_table.class')
            ->join('course_table', 'course_table.id = staff_course_table.course')
            ->join(
                'result_table',
                function ($join) {
                    $join->on('result_table.class', '=', 'class_table.id')
                        ->on('result_table.course', '=', 'course_table.id');
                },
                'LEFT'
            )
            ->where('staff_course_table.ref_no', $filters['teacher_id'])
            ->where('staff_course_table.term', $filters['term'])
            ->where('staff_course_table.year', $filters['year'])
            ->groupBy(['class_id', 'class_name', 'course_id', 'course_name'])
            ->orderBy(['class_name' => 'ASC', 'course_name' => 'ASC'])
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $classId = $row['class_id'];

            if (!isset($grouped[$classId])) {
                $grouped[$classId] = [
                    'class_id' => $row['class_id'],
                    'class_name' => $row['class_name'],
                    'level_id' => $row['level_id'],
                    'courses' => [],
                ];
            }

            $grouped[$classId]['courses'][] = [
                'course_id' => $row['course_id'],
                'course_name' => $row['course_name'],
            ];
        }

        return array_values($grouped);
    }

    private function getStaffFormClasses($teacherId): array
    {
        $rows = $this->level
            ->select([
                'level_table.id AS level_id',
                'level_table.level_name',
                'class_table.id AS class_id',
                'class_table.class_name'
            ])
            ->join('class_table', 'level_table.id = class_table.level')
            ->where('class_table.form_teacher', $teacherId)
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $levelId = $row['level_id'];

            if (!isset($grouped[$levelId])) {
                $grouped[$levelId] = [
                    'level_id' => $row['level_id'],
                    'level_name' => $row['level_name'],
                    'classes' => []
                ];
            }

            $grouped[$levelId]['classes'][] = [
                'class_id' => $row['class_id'],
                'class_name' => $row['class_name'],
            ];
        }

        return array_values($grouped);
    }

    public function getAdminOverview(int $term): array
    {
        return [
            'totals' => [
                'students' => $this->getTotalStudents(null),
                'staff'    => $this->getTotalTeachers(),
                'classes'  => $this->getTotalClasses(),
                'levels'   => $this->getTotalLevels(),
            ],
            'feeds' => $this->getFeeds($term),
        ];
    }

    /**
     * Student Dashboard Overview
     */
    public function getStudentOverview(array $filters): array
    {
        return [
            'recent_quizzes' => $this->getStudentRecentQuiz($filters),
            'feeds' => $this->getFeeds($filters['term']),
        ];
    }

    /**
     * Staff Dashboard Overview
     */
    public function getStaffOverview(array $filters): array
    {
        $classes = $this->getStaffAssignedCourses($filters);

        $classIds = array_column($classes, 'class_id');
        $courseIds = [];
        foreach ($classes as $class) {
            $courseIds = array_merge($courseIds, array_column($class['courses'], 'course_id'));
        }

        return [
            'recent_activities' => $this->getRecentActivities(
                [
                    'term' => $filters['term'],
                    'course_ids' => $courseIds,
                    'class_ids'  => $classIds,
                ]
            ),
            'form_classes' => array_map(function ($formClass) {
                foreach ($formClass['classes'] as &$class) {
                    $class['total_students'] = $this->getTotalStudents($class['class_id']);
                }
                return $formClass;
            }, $this->getStaffFormClasses($filters['teacher_id'])),
            'assigned_courses' => $classes,
            'feeds' => $this->getFeeds($filters['term']),
        ];
    }
}
