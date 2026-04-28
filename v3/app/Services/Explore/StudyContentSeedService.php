<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\StudySubTopic;
use V3\App\Models\Explore\StudyTopic;
use V3\App\Models\Explore\StudyCategory;

class StudyContentSeedService
{
    private StudyTopic $studyTopic;
    private StudyCategory $studyCategory;
    private StudySubTopic $studySubTopic;

    public function __construct(\PDO $pdo)
    {
        $this->studyTopic = new StudyTopic($pdo);
        $this->studyCategory = new StudyCategory($pdo);
        $this->studySubTopic = new StudySubTopic($pdo);
    }

    public function readContentFromJson(): array
    {
        $basePath = realpath(__DIR__ . '/LessonData');

        $topicsPath = realpath($basePath . '/topics.json');
        $topicsContent = file_get_contents($topicsPath);

        $subTopicsPath = realpath($basePath . '/sub_subtopics.json');
        $subTopicsContent = file_get_contents($subTopicsPath);

        $subSubTopicsPath = realpath($basePath . '/sub_sub_subtopics.json');
        $subSubTopicsContent = file_get_contents($subSubTopicsPath);

        if ($topicsContent === false || $subTopicsContent === false || $subSubTopicsContent === false) {
            throw new \Exception('Failed to read JSON file');
        }

        return [
            'topics' => json_decode($topicsContent, true),
            'subTopics' => json_decode($subTopicsContent, true),
            'subSubTopics' => json_decode($subSubTopicsContent, true),
        ];
    }

    public function seedStudyContent(): void
    {
        $courseName = 'Chemistry';
        $courseId = 7;
        $contentData = $this->readContentFromJson();
        $topicData = $contentData['topics'];

        foreach ($topicData as $topic) {
            // Insert category
            $categoryId = $this->studyCategory->insert([
                'title' => $topic['category'],
                'course_id' => $courseId,
                'course_name' => $courseName,
            ]);

            foreach ($topic['topics'] as $topicData) {
                // Insert topic
                $topicId = $this->studyTopic->insert([
                    'title' => $topicData['topic'],
                    'course_id' => $courseId,
                    'course_name' => $courseName,
                    'category_id' => $categoryId,
                ]);

                $subTopicData = array_filter(
                    $contentData['subTopics'],
                    fn($subTopic) => $subTopic['topic_id'] == $topicData['id']
                );

                foreach ($subTopicData['subtopics'] as $subTopic) {
                    // Insert subtopic
                    $subSubTopicData = array_filter(
                        $contentData['subSubTopics'],
                        fn($subSubTopic) => $subSubTopic['topic_id'] == $topicData['id']
                    );

                    $this->studySubTopic->insert([
                        'title' => $subTopic['name'],
                        'course_id' => $courseId,
                        'course_name' => $courseName,
                        'category_id' => $categoryId,
                        'topic_id' => $topicId,
                        'sub_subtopics' => json_encode($subSubTopicData['sub_subtopics'] ?? [])
                    ]);
                }
            }
        }
    }
}
