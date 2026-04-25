<?php

namespace V3\App\Services\Explore;

use  V3\App\Models\Explore\StudyTopic;

class StudyTopicService
{
    private StudyTopic $studyTopic;

    public function __construct(\PDO $pdo)
    {
        $this->studyTopic = new StudyTopic($pdo);
    }

    public function addTopic(array $data): int
    {
        return $this->studyTopic->insert([
            'title' => $data['title'],
            'subtopics_json' => json_encode($data['sub_topics']),
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'category_id' => $data['category_id'] ?? null,
            'category_name' => $data['category_name'] ?? null,
        ]);
    }

    public function updateTopic(int $topicId, array $data): bool
    {
        return $this->studyTopic
            ->where('id', $topicId)
            ->update([
                'title' => $data['title'],
                'subtopics_json' => json_encode($data['sub_topics']),
                'course_id' => $data['course_id'],
                'course_name' => $data['course_name'],
                'category_id' => $data['category_id'] ?? null,
                'category_name' => $data['category_name'] ?? null,
            ]);
    }

    public function getAllTopics(): array
    {
        $rows = $this->studyTopic
            ->select([
                'id AS topic_id',
                'title AS topic_name',
                'course_id',
                'course_name',
                'category_id',
                'category_name',
                'subtopics_json',
            ])
            ->orderBy('course_name')
            ->orderBy('title')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $courseId = $row['course_id'];
            $courseName = $row['course_name'];

            if (!isset($grouped[$courseId])) {
                $grouped[$courseId] = [
                    'course_id' => $courseId,
                    'course_name' => $courseName,
                    'topics' => [],
                ];
            }

            $grouped[$courseId]['topics'][] = [
                'topic_id' => $row['topic_id'],
                'topic_name' => $row['topic_name'],
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'sub_topics' => json_decode($row['subtopics_json'], true) ?? [],
            ];
        }

        return array_values($grouped);
    }

    public function getTopicsByCourseId(int $courseId): array
    {
        return $this->studyTopic
            ->select([
                'id AS topic_id',
                'title AS topic_name',
                'course_id',
                'course_name',
                'category_id',
                'category_name',
            ])
            ->where('course_id', $courseId)
            ->get();
    }
}
