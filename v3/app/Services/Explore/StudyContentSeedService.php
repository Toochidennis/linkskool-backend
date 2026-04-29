<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\StudySubTopic;
use V3\App\Models\Explore\StudyTopic;
use V3\App\Models\Explore\StudyCategory;

class StudyContentSeedService
{
    private \PDO $pdo;
    private StudyTopic $studyTopic;
    private StudyCategory $studyCategory;
    private StudySubTopic $studySubTopic;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->studyTopic = new StudyTopic($pdo);
        $this->studyCategory = new StudyCategory($pdo);
        $this->studySubTopic = new StudySubTopic($pdo);
    }

    public function readContentFromJson(): array
    {
        $basePath = realpath(__DIR__ . '/LessonData');

        if ($basePath === false) {
            throw new \RuntimeException('LessonData directory not found');
        }

        return [
            'topics' => $this->readJsonFile($basePath . '/topics.json'),
            'subTopics' => $this->readJsonFile($basePath . '/sub_topics.json'),
            'subSubTopics' => $this->readJsonFile($basePath . '/sub_subtopics.json'),
        ];
    }

    public function seedStudyContent(): void
    {
        $courseName = 'Chemistry';
        $courseId = 7;
        $contentData = $this->readContentFromJson();

        $subTopicsByTopicId = $this->indexBy($contentData['subTopics'], 'topic_id');
        $subSubTopicsBySubTopicId = $this->indexBy($contentData['subSubTopics'], 'subtopic_id');

        $this->pdo->beginTransaction();

        try {
            foreach ($contentData['topics'] as $categoryData) {
                $categoryName = $categoryData['category'];

                $categoryId = $this->studyCategory->insert([
                    'title' => $categoryName,
                    'course_id' => $courseId,
                    'course_name' => $courseName,
                ]);

                if ($categoryId === false) {
                    throw new \RuntimeException("Failed to insert study category: {$categoryName}");
                }

                foreach ($categoryData['topics'] as $topicData) {
                    $sourceTopicId = (string) $topicData['id'];
                    $subTopics = $subTopicsByTopicId[$sourceTopicId]['subtopics'] ?? [];

                    $topicId = $this->studyTopic->insert([
                        'title' => $topicData['topic'],
                        'course_id' => $courseId,
                        'course_name' => $courseName,
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                    ]);

                    if ($topicId === false) {
                        throw new \RuntimeException("Failed to insert study topic: {$topicData['topic']}");
                    }

                    foreach ($subTopics as $subTopic) {
                        $sourceSubTopicId = (string) $subTopic['id'];
                        $subSubTopics = $subSubTopicsBySubTopicId[$sourceSubTopicId]['sub_subtopics'] ?? [];

                        $subTopicId = $this->studySubTopic->insert([
                            'title' => $subTopic['name'],
                            'course_id' => $courseId,
                            'course_name' => $courseName,
                            'category_id' => $categoryId,
                            'topic_id' => $topicId,
                            'sub_subtopics' => $this->toJson($subSubTopics),
                        ]);

                        if ($subTopicId === false) {
                            throw new \RuntimeException("Failed to insert study subtopic: {$subTopic['name']}");
                        }
                    }
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function readJsonFile(string $path): array
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new \RuntimeException("Failed to read JSON file: {$path}");
        }

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!\is_array($decoded)) {
            throw new \RuntimeException("JSON file must contain an array: {$path}");
        }

        return $decoded;
    }

    private function indexBy(array $rows, string $key): array
    {
        $indexed = [];

        foreach ($rows as $row) {
            if (!isset($row[$key])) {
                continue;
            }

            $indexed[(string) $row[$key]] = $row;
        }

        return $indexed;
    }

    private function buildTopicSubtopics(array $subTopics, array $subSubTopicsBySubTopicId): array
    {
        return array_map(function (array $subTopic) use ($subSubTopicsBySubTopicId): array {
            $sourceSubTopicId = (string) $subTopic['id'];

            return [
                'id' => $subTopic['id'],
                'name' => $subTopic['name'],
                'sub_subtopics' => $subSubTopicsBySubTopicId[$sourceSubTopicId]['sub_subtopics'] ?? [],
            ];
        }, $subTopics);
    }

    private function toJson(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
