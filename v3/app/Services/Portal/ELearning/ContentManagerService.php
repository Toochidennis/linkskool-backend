<?php

namespace V3\App\Services\Portal\ELearning;

use PDO;
use V3\App\Common\Enums\ContentType;
use V3\App\Models\Portal\ELearning\Content;
use V3\App\Models\Portal\ELearning\Quiz;

class ContentManagerService
{
    private Content $content;
    private Quiz $quiz;

    public function __construct(PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->quiz = new Quiz($pdo);
    }

    private function getQuestions(array $questionIds)
    {
        $questions = [];
        foreach ($questionIds as $questionId) {
            $question = $this->quiz
                ->select([
                    'question_id',
                    'parent AS question_grade',
                    'content AS question_files',
                    'title AS question_text',
                    'type AS question_type',
                    'answer AS options',
                    'correct',
                ])
                ->where('question_id', '=', $questionId['id'])
                ->first();

            if (!empty($question)) {
                $questions[] = array_map(function ($row) {
                    $row['question_files'] = $this->json($row['question_files'] ?? []);
                    $row['options'] = $this->json($row['options'] ?? '{}');
                    $row['correct'] = $this->json($row['correct']);
                    return $row;
                }, $question);
            }
        }

        return $questions;
    }

    private function appendQuestionsToContent($content)
    {
        $questionIds = json_decode($content['url'] ?? [], true);
        if (is_array($questionIds) && count($questionIds)) {
            $questions = $this->getQuestions($questionIds);
            $content['questions'] = $questions;
        } else {
            $content['questions'] = [];
        }
        return $content;
    }

    public function getContents(int $syllabusId)
    {
        // Step 1. Fetch all contents for the syllabus
        $contents = $this->content
            ->select()
            ->where('outline', '=', $syllabusId)
            ->orderBy('rank') // adjust if you have another ordering
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
                    // orphan child → treat as no topic
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
                'type' => $topic['type'],
                'children' => []
            ];

            if (isset($contentByTopic[$topicId])) {
                foreach ($contentByTopic[$topicId] as $child) {
                    // Check if content is a quiz (type 2)
                    if ($child['type'] == ContentType::QUIZ->value && $child['url']) {
                        $child = $this->appendQuestionsToContent($child);
                    }
                    $topicGroup['children'][] = $child;
                }
            }

            $result[] = $topicGroup;
        }

        // Add contents that have no topic (standalone items)
        foreach ($noTopic as $standalone) {
            if ($standalone['type'] == ContentType::QUIZ->value && $standalone['url']) {
                $standalone = $this->appendQuestionsToContent($standalone);
            }
            $result[] = $standalone;
        }

        return $result;
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
