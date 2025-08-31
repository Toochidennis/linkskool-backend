<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Common\Enums\QuestionType;
use V3\App\Models\Portal\ELearning\Content;
use V3\App\Models\Portal\ELearning\Quiz;
use V3\App\Models\Portal\Results\Result;

class StudentContentManagerService
{
    private Content $content;
    private Quiz $quiz;
    private Result $result;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->quiz = new Quiz($pdo);
        $this->result = new Result($pdo);
    }

    private function getCourses(array $filters): array
    {
        $courses = [];

        $syllabi = $this->content
            ->select(columns: ['id', 'title', 'path_label', 'course_id', 'course_name', 'level'])
            ->where('term', '=', $filters['term'])
            ->where('level', '=', $filters['level_id'])
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

        if (empty($contents)) {
            return [];
        }

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
            $question['question_type'] = QuestionType::tryFrom($question['question_type'])?->label();
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
                'date_posted' => $content['end_date'],
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

    private function hasClass(array $pathLabel, string|int $targetClassId): bool
    {
        return !empty(array_filter($pathLabel, fn($cls) => $cls['id'] == $targetClassId));
    }
}
