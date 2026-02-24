<?php

namespace V3\App\Services\Explore;

class AutoGradeService
{
    private const MAX_RETRIES = 3;
    private const RETRY_BACKOFF_MS = 1000;
    private const MAX_COMMENT_WORDS = 20;

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

    private function callAIWithRetry(string $prompt, int $retryCount = 0): array
    {
        try {
            $payload = [
                'model' => 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a strict academic grader. Return only JSON.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.2,
                'max_tokens' => 220,
            ];

            $ch = curl_init(getenv('DEEP_SEEK_URL'));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . getenv('DEEP_SEEK_API_KEY'),
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new \RuntimeException('DeepSeek request failed: ' . $curlError);
            }

            if ($httpCode === 429) {
                if ($retryCount < self::MAX_RETRIES) {
                    $backoffMs = self::RETRY_BACKOFF_MS * (2 ** $retryCount);
                    usleep((int)($backoffMs * 1000));
                    return $this->callAIWithRetry($prompt, $retryCount + 1);
                }

                throw new \RuntimeException('DeepSeek API rate limit exceeded after retries');
            }

            if ($httpCode >= 400) {
                throw new \RuntimeException("DeepSeek API error: HTTP {$httpCode}");
            }

            $decoded = json_decode($response, true);
            $content = $decoded['choices'][0]['message']['content'] ?? '';

            if (!\is_string($content) || trim($content) === '') {
                throw new \RuntimeException('Invalid DeepSeek response structure');
            }

            $parsed = $this->parseResponseContent($content);

            if (!isset($parsed['score']) || !isset($parsed['comment'])) {
                throw new \RuntimeException('Missing score/comment in AI response');
            }

            return [
                'score' => max(0, min(100, (float)$parsed['score'])),
                'comment' => $this->humanizeComment((string) ($parsed['comment'] ?? '')),
            ];
        } catch (\Throwable $e) {
            if ($retryCount < self::MAX_RETRIES) {
                $backoffMs = self::RETRY_BACKOFF_MS * (2 ** $retryCount);
                usleep((int)($backoffMs * 1000));
                return $this->callAIWithRetry($prompt, $retryCount + 1);
            }

            return [
                'score' => 0,
                'comment' => 'AI grading failed.',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function parseResponseContent(string $content): array
    {
        $trimmed = trim($content);

        if (strpos($trimmed, '```json') !== false) {
            preg_match('/```json\s*(.*?)\s*```/s', $trimmed, $matches);
            $trimmed = $matches[1] ?? $trimmed;
        } elseif (strpos($trimmed, '```') !== false) {
            preg_match('/```\s*(.*?)\s*```/s', $trimmed, $matches);
            $trimmed = $matches[1] ?? $trimmed;
        }

        $parsed = json_decode($trimmed, true);

        if (\is_array($parsed)) {
            return $parsed;
        }

        return [];
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
