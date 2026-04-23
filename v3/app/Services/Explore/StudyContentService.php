<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\StudyTopicExamType;

class StudyContentService
{
    protected StudyTopicExamType $studyTopicExamType;

    public function __construct(\PDO $pdo)
    {
        $this->studyTopicExamType = new StudyTopicExamType($pdo);
    }

    public function getTopicByExamType(int $examTypeId, int $courseId): array
    {
        $topics = $this->studyTopicExamType->rawQuery(
            'SELECT
                t.id AS topic_id,
                t.title AS topic_name,
                t.category_id AS category_id,
                c.title AS category_name
             FROM study_topic_exam_types ste
             INNER JOIN study_topics t ON ste.topic_id = t.id
             LEFT JOIN study_categories c ON t.category_id = c.id
             WHERE ste.exam_type_id = ?
               AND ste.course_id = ?
             ORDER BY ste.display_order ASC, c.title ASC, t.title ASC',
            [$examTypeId, $courseId]
        );

        $grouped = [];

        foreach ($topics as $row) {
            $categoryId = $row['category_id'];
            $categoryName = $row['category_name'] ?? null;

            $groupKey = $categoryId ?? 'General';

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'topics' => [],
                ];
            }

            $grouped[$groupKey]['topics'][] = [
                'topic_id' => $row['topic_id'],
                'topic_name' => $row['topic_name'],
            ];
        }

        return array_values($grouped);
    }

    public function getTopicContentByTopicId(int $topicId): ?array
    {
        $topic = $this->studyTopicExamType->rawQuery(
            'SELECT
                t.id AS topic_id,
                t.title AS topic_name,
                t.content_json,
                t.course_id,
                t.course_name,
                t.category_id,
                c.title AS category_name
             FROM study_topics t
             LEFT JOIN study_categories c ON t.category_id = c.id
             WHERE t.id = ?',
            [$topicId]
        );

        if (empty($topic)) {
            return null;
        }

        $row = $topic[0];

        return [
            'topic_id' => $row['topic_id'],
            'topic_name' => $row['topic_name'],
            'course_id' => $row['course_id'],
            'course_name' => $row['course_name'],
            'category_id' => $row['category_id'],
            'category_name' => $row['category_name'] ?? null,
            'content' => json_decode($row['content_json'], true) ?? [],
        ];
    }
}
