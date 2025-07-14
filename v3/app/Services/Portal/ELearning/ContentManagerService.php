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

    private array $contentTypeNames = [
        ContentType::TOPIC->value => 'topic',
        ContentType::QUIZ->value => 'quiz',
        ContentType::MATERIAL->value => 'material',
        ContentType::ASSIGNMENT->value => 'assignment',
    ];

    private array $questionTypeNames = [
        'qo' => 'multiple_choice',
        'qs' => 'short_answer',
    ];

    /**
     * ContentManagerService constructor.
     *
     * @param PDO $pdo
     */

    public function __construct(PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->quiz = new Quiz($pdo);
    }

    private function getQuestions(array $questionIds): array
    {
        $ids = array_values(array_map(
            fn($item) => (int)$item['id'],
            array_filter($questionIds, fn($item) => $item['rank'] != 0)
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
            $question['question_type'] = $this->questionTypeNames[$question['question_type']]
                ?? $question['question_type'];
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
                'type' => $this->contentTypeNames[$topic['type']],
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

    private function formatContent(array $content): array
    {
        // MATERIAL
        if ($content['type'] == ContentType::MATERIAL->value) {
            return [
                'id' => $content['id'],
                'syllabus_id' => $content['outline'],
                'title' => $content['title'],
                'description' => $content['description'],
                'type' => $this->contentTypeNames[$content['type']],
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
                'type' => $this->contentTypeNames[$content['type']],
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
                    'type' => $this->contentTypeNames[$content['type']],
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

    private function json(string $data): array
    {
        if (empty($data)) {
            return [];
        }
        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : [];
    }
}
