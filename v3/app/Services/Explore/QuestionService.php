<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Enums\QuestionType;
use V3\App\Models\Portal\ELearning\Quiz;

class QuestionService
{
    private Quiz $quiz;

    public function __construct(\PDO $pdo)
    {
        $this->quiz = new Quiz($pdo);
    }

    /**
     * Fetch questions by their IDs and format them
     *
     * @param array $questionIds Array of question IDs to fetch
     * @param array $options Optional parameters (limit, offset, shuffle)
     * @return array Formatted questions array
     */
    public function fetchQuestions(array $questionIds, array $options = []): array
    {
        if (empty($questionIds)) {
            return [];
        }

        // Apply shuffle if requested
        if ($options['shuffle'] ?? false) {
            \shuffle($questionIds);
        }

        // Apply limit if specified
        if (isset($options['limit']) && $options['limit'] > 0) {
            $questionIds = \array_slice($questionIds, 0, $options['limit']);
        }

        $query = $this->quiz
            ->select([
                'question_id',
                'title AS question_text',
                'content AS question_files',
                'topic',
                'topic_id',
                'passage',
                'passage_id',
                'instruction',
                'instruction_id',
                'explanation',
                'explanation_id',
                'type as question_type',
                'answer as options',
                'correct',
                'year'
            ])
            ->in('question_id', $questionIds);

        if (!empty($options['pagination'])) {
            $questions = $query->paginate($options['page'], $options['limit']);

            return [
                'data' => array_map($this->formatQuestion(...), $questions['data']),
                'meta' => $questions['meta']
            ];
        }

        // Apply pagination if specified
        if (isset($options['limit'])) {
            $query->limit($options['limit']);
        }
        if (isset($options['offset'])) {
            $query->offset($options['offset']);
        }

        $questions = $query->get();

        if (empty($questions)) {
            return [];
        }

        return array_map([$this, 'formatQuestion'], $questions);
    }

    /**
     * Format a single question by decoding JSON fields
     *
     * @param array $question Raw question data from database
     * @return array Formatted question
     */
    private function formatQuestion(array $question): array
    {
        $question['question_type'] = QuestionType::tryFrom($question['question_type'])?->label() ?? 'Unknown';
        $question['question_files'] = $this->decode($question['question_files']);
        $question['options'] = $this->decode($question['options']);
        $question['correct'] = $this->decode($question['correct']);

        return $question;
    }

    /**
     * Decode JSON string to array
     *
     * @param string|null $data JSON string
     * @return array Decoded array or empty array
     */
    private function decode(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return \is_array($decoded) ? $decoded : [];
    }
}
