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
            'sub_topics' => json_encode($data['sub_topics']),
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'category_id' => $data['category_id'] ?? null,
        ]);
    }

    public function updateTopic(int $topicId, array $data): bool
    {
        return $this->studyTopic
            ->where('id', $topicId)
            ->update([
                'title' => $data['title'],
                'sub_topics' => json_encode($data['sub_topics']),
                'course_id' => $data['course_id'],
                'course_name' => $data['course_name'],
                'category_id' => $data['category_id'] ?? null,
            ]);
    }

    public function getAllTopics(): array
    {
        return $this->studyTopic
            ->get();
    }

    public function getTopicsByCourseId(int $courseId): array
    {
        return $this->studyTopic
            ->where('course_id', $courseId)
            ->get();
    }
}
