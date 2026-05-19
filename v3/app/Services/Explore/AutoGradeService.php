<?php

namespace V3\App\Services\Explore;

use V3\App\Services\Common\DeepSeekClient;

class AutoGradeService
{
    private const MAX_COMMENT_WORDS = 20;

    private DeepSeekClient $ai;

    public function __construct()
    {
        $this->ai = new DeepSeekClient();
    }

    public function grade(string $questionText, string $answerText): array
    {
        $prompt = $this->buildPrompt($questionText, $answerText);
        return $this->callAIWithRetry($prompt);
    }

    private function buildPrompt(string $question, string $answer): string
    {
        return <<<PROMPT
            You are a strict academic teacher grading student work.

            Follow ALL rules exactly.

            Return ONLY valid JSON.
            Do not include explanations.
            Do not include markdown.
            Do not include text before or after the JSON.

            JSON format:
            {
            "score": number between 0 and 100,
            "comment": "One short sentence, maximum 20 words"
            }

            Comment rules:
            - Exactly 1 sentence.
            - Maximum 20 words.
            - Sound natural and human.
            - Be direct and constructive.
            - Mention what was missing.
            - Suggest one clear improvement.
            - No robotic phrases like "the response is" or "this constitutes".

            Score rules:
            - 0-49 = major gaps.
            - 50-69 = basic understanding but missing depth.
            - 70-84 = good but incomplete.
            - 85-100 = strong and well-developed.

            QUESTION:
            {$question}

            STUDENT ANSWER:
            {$answer}
            PROMPT;
    }

    private function callAIWithRetry(string $prompt): array
    {
        try {
            $result = $this->ai->call([
                'model'       => 'deepseek-chat',
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are a strict academic grader. Return only JSON.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.2,
                'max_tokens'  => 220,
            ], 30);

            if (!isset($result['score']) || !isset($result['comment'])) {
                throw new \RuntimeException('Missing score/comment in response');
            }

            return [
                'score'   => max(0, min(100, (float) $result['score'])),
                'comment' => $this->humanizeComment((string) ($result['comment'] ?? '')),
            ];
        } catch (\Throwable $e) {
            return [
                'score'   => 0,
                'comment' => 'Grading failed.',
                'error'   => $e->getMessage(),
            ];
        }
    }

    private function humanizeComment(string $comment): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $comment) ?? '');
        if ($clean === '') {
            return 'Needs more direct answers to the task.';
        }

        $words = preg_split('/\s+/', $clean) ?: [];
        if (count($words) > self::MAX_COMMENT_WORDS) {
            $words = array_slice($words, 0, self::MAX_COMMENT_WORDS);
            $clean = rtrim(implode(' ', $words), " \t\n\r\0\x0B.,;:!?") . '.';
        }

        return $clean;
    }
}
