<?php

namespace V3\App\Services\Explore;

class AutoGradeService
{
    private const MAX_RETRIES = 3;
    private const RETRY_BACKOFF_MS = 1000;

    public function grade(string $questionText, string $answerText): array
    {
        $prompt = $this->buildPrompt($questionText, $answerText);
        return $this->callAIWithRetry($prompt);
    }

    private function buildPrompt(string $question, string $answer): string
    {
        return <<<PROMPT
            You are a strict academic grader.

            Grade the student answer strictly.

            Return ONLY valid JSON in this format:
            {
            "score": number between 0 and 100,
            "comment": "Detailed academic feedback (max 60 words)"
            }

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
                'comment' => trim((string)$parsed['comment']),
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
}
