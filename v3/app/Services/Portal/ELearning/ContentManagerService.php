<?php

namespace V3\App\Services\Portal\ELearning;

use PDO;
use V3\App\Common\Enums\ContentType;
use V3\App\Common\Enums\QuestionType;
use V3\App\Common\Utilities\PathResolver;
use V3\App\Models\Portal\Academics\ClassModel;
use V3\App\Models\Portal\ELearning\Content;
use V3\App\Models\Portal\ELearning\Quiz;

class ContentManagerService
{
    private Content $content;
    private Quiz $quiz;
    private string $contentPath;
    private ClassModel $classModel;

    /**
     * ContentManagerService constructor.
     *
     * @param PDO $pdo
     */

    public function __construct(PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->quiz = new Quiz($pdo);
        $this->classModel = new ClassModel($pdo);

        $paths = PathResolver::getContentPaths();
        $this->contentPath = $paths['absolute'];
    }

    private function getRecentContent(
        int $term,
        ?string $type = null,
        array $filters = []
    ): array {
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
            ->where('term', '=', $term)
            ->orderBy('upload_date', 'DESC');

        if ($type !== null) {
            $query->where('type', '=', $type);
        }

        $results = $query->get();
        if (empty($results)) {
            return [];
        }

        $items = [];
        foreach ($results as $result) {
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
            ];

            // only activities have comment/body
            if ($type === null || $type !== ContentType::QUIZ->value) {
                $item['comment'] = $result['body'] ?? '';
            }

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

    public function getStaffAssignedCourses(array $filters): array
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

    public function getDashboard(array $filters): array
    {
        $term = $filters['term'];

        if ($filters['role'] === 'admin') {
            return [
                'recent_quizzes' => $this->getRecentContent($term, ContentType::QUIZ->value),
                'recent_activities' => $this->getRecentContent($term),
            ];
        }

        if ($filters['role'] === 'staff') {
            $classes = $this->getStaffAssignedCourses($filters);

            $classIds = array_column($classes, 'class_id');
            $courseIds = [];
            foreach ($classes as $class) {
                $courseIds = array_merge($courseIds, array_column($class['courses'], 'course_id'));
            }

            $staffFilter = [
                'class_ids' => $classIds,
                'course_ids' => $courseIds,
            ];

            return [
                'recent_quizzes'   => $this->getRecentContent($term, ContentType::QUIZ->value, $staffFilter),
                'recent_activities' => $this->getRecentContent($term, null, $staffFilter),
                'courses' => $classes,
            ];
        }

        return [];
    }

    public function getContent(int $contentId): array
    {
        $content = $this->content
            ->where('id', '=', $contentId)
            ->first();

        if (!$content) {
            return [];
        }

        if ($content['type'] == ContentType::QUIZ->value && $content['url']) {
            return $this->appendQuestionsToContent($content);
        }

        return $this->formatContent($content);
    }

    public function getContents(int $syllabusId): array
    {
        $contents = $this->content
            ->where('outline', '=', $syllabusId)
            ->orderBy('rank')
            ->get();

        $result = [];

        $topics = [];          // topic_id → topic content object
        $contentByTopic = [];  // topic_id → array of contents under this topic
        $noTopic = [];         // contents without parent topics

        // Separate contents into topics vs. others
        foreach ($contents as $content) {
            if ($content['type'] == ContentType::TOPIC->value) {
                $topics[$content['id']] = $content;
            } else {
                if ($content['parent'] && isset($topics[$content['parent']])) {
                    $contentByTopic[$content['parent']][] = $content;
                } elseif ($content['parent'] && !isset($topics[$content['parent']])) {
                    $noTopic[] = $content;
                } else {
                    $noTopic[] = $content;
                }
            }
        }

        // Process topics and add their children
        foreach ($topics as $topicId => $topic) {
            $topicGroup = [
                'id' => $topic['id'],
                'title' => $topic['title'],
                'type' => ContentType::TOPIC->label(),
                'objective' => $topic['body'],
                'classes' => $this->json($topic['path_label']),
                'rank' => $topic['rank'] ?? 0,
                'children' => []
            ];

            if (isset($contentByTopic[$topicId])) {
                foreach ($contentByTopic[$topicId] as $child) {
                    $child = ($child['type'] == ContentType::QUIZ->value && $child['url'])
                        ?
                        $this->appendQuestionsToContent($child)
                        :
                        $this->formatContent($child);

                    $topicGroup['children'][] = $child;
                }
            }

            $result[] = $topicGroup;
        }

        // Add contents that have no topic (standalone items)
        if (!empty($noTopic)) {
            $noTopicGroup = [
                'id' => null,
                'title' => 'No Topic',
                'type' => 'no topic',
                'children' => [],
            ];

            foreach ($noTopic as $standalone) {
                $standalone = ($standalone['type'] == ContentType::QUIZ->value && $standalone['url'])
                    ?
                    $this->appendQuestionsToContent($standalone)
                    :
                    $this->formatContent($standalone);

                $noTopicGroup['children'][] = $standalone;
            }

            $result[] = $noTopicGroup;
        }

        return $result;
    }

    private function getQuestions(array $questionIds): array
    {
        $ids = array_values(array_map(
            fn($item) => (int)$item['id'],
            $questionIds
        ));

        if (empty($ids)) {
            return [];
        }

        $questions = $this->quiz
            ->select([
                'question_id',
                'parent AS question_grade',
                'content AS question_files',
                'title AS question_text',
                'type AS question_type',
                'answer AS options',
                'correct',
            ])
            ->in('question_id', $ids)
            ->get();

        if (!$questions) {
            return [];
        }

        $questions = array_map(function ($question) {
            $question['question_type'] = QuestionType::tryFrom($question['question_type'])?->label() ?? 'Unknown';
            $question['question_files'] = $this->json($question['question_files']);
            $question['options'] = $this->json($question['options']);
            $question['correct'] = $this->json($question['correct']);
            return $question;
        }, $questions);

        return $questions;
    }

    private function appendQuestionsToContent($content)
    {
        $questionIds = $this->json($content['url']);
        if (is_array($questionIds) && count($questionIds)) {
            $questions = $this->getQuestions($questionIds);
            $content['questions'] = $questions;
        } else {
            $content['questions'] = [];
        }
        return $this->formatContent($content);
    }

    private function formatContent(array $content): array
    {
        // MATERIAL
        if ($content['type'] == ContentType::MATERIAL->value) {
            return [
                'id' => $content['id'],
                'syllabus_id' => $content['outline'],
                'title' => $content['title'],
                'description' => $content['description'],
                'type' => ContentType::MATERIAL->label(),
                'rank' => $content['rank'] ?? 0,
                'topic_id' => $content['parent'] ?? 0,
                'topic' => $content['category'] ?? '',
                'classes' => $this->json($content['path_label']),
                'content_files' => $this->json($content['url']),
                'date_posted' => $content['upload_date'],
            ];
        }

        // ASSIGNMENT
        if ($content['type'] == ContentType::ASSIGNMENT->value) {
            return [
                'id' => $content['id'],
                'syllabus_id' => $content['outline'],
                'title' => $content['title'],
                'description' => $content['description'],
                'type' => ContentType::ASSIGNMENT->label(),
                'rank' => $content['rank'] ?? 0,
                'topic_id' => $content['parent'] ?? 0,
                'topic' => $content['category'] ?? '',
                'classes' => $this->json($content['path_label']),
                'start_date' => $content['start_date'],
                'end_date' => $content['end_date'],
                'grade' => $content['body'],
                'content_files' => $this->json($content['url']),
                'date_posted' => $content['upload_date'],
            ];
        }

        // QUIZ
        if ($content['type'] == ContentType::QUIZ->value) {
            return [
                'settings' => [
                    'id' => $content['id'],
                    'syllabus_id' => $content['outline'],
                    'title' => $content['title'],
                    'description' => $content['description'],
                    'type' => ContentType::QUIZ->label(),
                    'rank' => $content['rank'] ?? 0,
                    'topic_id' => $content['parent'] ?? 0,
                    'topic' => $content['category'] ?? '',
                    'classes' => $this->json($content['path_label']),
                    'start_date' => $content['start_date'],
                    'end_date' => $content['end_date'],
                    'duration' => $content['body'],
                    'date_posted' => $content['upload_date'],
                ],
                'questions' => $content['questions'],
            ];
        }

        throw new \RuntimeException("Unknown content type: {$content['type']}");
    }

    private function json(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function deleteContent($id): bool
    {
        $content = $this->content
            ->where('id', '=', $id)
            ->first();

        if (empty($content)) {
            throw new \Exception("Content not found for ID: $id");
        }

        $type = $content['type'];

        // Handle specific deletion logic based on type
        match ($type) {
            ContentType::QUIZ->value => $this->deleteQuizContent($content),
            ContentType::MATERIAL->value, ContentType::ASSIGNMENT->value => $this->deleteContentFiles($content),
            ContentType::TOPIC->value => $this->deleteTopicAndUpdateChildren($id),
            ContentType::SYLLABUS->value => $this->deleteSyllabusIfNoChildren($id),
            default => throw new \Exception("Unknown content type: {$type}")
        };

        // Finally delete the content row
        $this->content
            ->where('id', '=', $id)
            ->delete();

        return true;
    }

    private function deleteContentFiles(array $content): void
    {
        $files = json_decode($content['url'], true, 512, JSON_THROW_ON_ERROR);
        if (is_array($files)) {
            $this->deleteFiles($files);
        }
    }
    private function deleteQuizContent(array $content): void
    {
        $quizItems = json_decode($content['url'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($quizItems as $item) {
            $questionId = $item['id'];
            $question = $this->quiz->where('question_id', '=', $questionId)->first();

            if (empty($question)) {
                continue;
            }

            $questionFiles = json_decode($question['content'], true);
            $this->deleteFiles($questionFiles);

            $options = json_decode($question['answer'], true);
            foreach ($options as $option) {
                if (!empty($option['option_files'] ?? [])) {
                    $this->deleteFiles($option['option_files']);
                }
            }

            $this->quiz
                ->where('question_id', '=', $questionId)
                ->delete();
        }
    }

    private function deleteTopicAndUpdateChildren(int $topicId): void
    {
        $this->content
            ->where('parent', '=', $topicId)
            ->update(['parent' => 0]);
    }

    private function deleteSyllabusIfNoChildren(int $syllabusId): void
    {
        $children = $this->content
            ->where('outline', '=', $syllabusId)
            ->count();
        if ($children > 0) {
            throw new \Exception("Cannot delete syllabus. It has dependent contents.");
        }
    }

    /**
     * Deletes a list of files from the filesystem
     */
    private function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            $filePath = $file['file_name'] ?? $file['old_file_name'] ?? null;

            if ($filePath) {
                $absolute = $this->contentPath . basename($filePath);
                if (file_exists($absolute)) {
                    @unlink($absolute); // Suppress warning, continue even if file fails
                }
            }
        }
    }
}
