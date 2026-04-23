<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\StudyTopic;
use V3\App\Models\Explore\StudyCategory;

class StudyContentSeedService
{
    private StudyTopic $studyTopic;
    private StudyCategory $studyCategory;

    public function __construct(\PDO $pdo)
    {
        $this->studyTopic = new StudyTopic($pdo);
        $this->studyCategory = new StudyCategory($pdo);
    }

    public function readContentFromJson(): array
    {
        $path = realpath(__DIR__ . '/LessonData/study_content.json');
        $jsonContent = file_get_contents($path);

        if ($jsonContent === false) {
            throw new \Exception('Failed to read JSON file');
        }
        return json_decode($jsonContent, true);
    }

    public function seedStudyContent(): void
    {
        $contentData = $this->readContentFromJson();

        foreach ($contentData['sections'] as $categoryData) {
            // Insert category
            $categoryId = $this->studyCategory->insert([
                'title' => $categoryData['section'],
                'course_id' => $contentData['courseId'],
                'course_name' => $contentData['courseName'],
            ]);

            foreach ($categoryData['topics'] as $topicData) {
                // Insert topic
                $this->studyTopic->insert([
                    'title' => $topicData['topic'],
                    'subtopics_json' => json_encode($topicData['subtopics']),
                    'course_id' => $contentData['courseId'],
                    'course_name' => $contentData['courseName'],
                    'category_id' => $categoryId,
                ]);
            }
        }
    }
}
