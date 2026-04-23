<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\StudyCategoryTopic;
use V3\App\Models\Explore\StudyTopic;
use V3\App\Models\Explore\StudyCategory;

class StudyContentSeedService
{
    private StudyTopic $studyTopic;
    private StudyCategoryTopic $studyCategoryTopic;
    private StudyCategory $studyCategory;

    public function __construct(\PDO $pdo)
    {
        $this->studyTopic = new StudyTopic($pdo);
        $this->studyCategoryTopic = new StudyCategoryTopic($pdo);
        $this->studyCategory = new StudyCategory($pdo);
    }

    public function readContentFromJson(): array
    {
        $jsonContent = file_get_contents('./LessonData/study_content.json');

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
                $topicId = $this->studyTopic->insert([
                    'title' => $topicData['topic'],
                    'sub_topics' => json_encode($topicData['sub_topics']),
                    'course_id' => $contentData['courseId'],
                    'course_name' => $contentData['courseName'],
                    'category_id' => $categoryId,
                ]);
            }
        }
    }
}
