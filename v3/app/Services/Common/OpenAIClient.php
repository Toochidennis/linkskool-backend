<?php

namespace V3\App\Services\Common;

class OpenAIClient
{
    private const MAX_RETRIES = 3;
    private const RETRY_BACKOFF_MS = 500;

    public function call(array $payload, int $timeout = 60): array
    {
        return $this->execute($payload, $timeout, 0);
    }

    private function execute(array $payload, int $timeout, int $retry): array
    {
        try {
            $ch = curl_init(getenv('OPENAI_API_URL'));

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . getenv('OPENAI_API_KEY'),
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response  = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new \RuntimeException('cURL request failed: ' . $curlError);
            }

            if ($httpCode === 429 || $httpCode >= 400) {
                throw new \RuntimeException("error: HTTP {$httpCode}");
            }

            $decoded = json_decode($response, true);
            $content = $decoded['choices'][0]['message']['content'] ?? null;

            if (!is_string($content) || trim($content) === '') {
                throw new \RuntimeException('Empty or invalid response');
            }

            return $this->extractJson($content);
        } catch (\Throwable $e) {
            if ($retry < self::MAX_RETRIES) {
                usleep((int)(self::RETRY_BACKOFF_MS * (2 ** $retry) * 1000));
                return $this->execute($payload, $timeout, $retry + 1);
            }

            throw $e;
        }
    }

    private function extractJson(string $content): array
    {
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        if (!preg_match('/\{.*\}/s', $content, $matches)) {
            throw new \RuntimeException('No JSON object found in AI response');
        }

        $decoded = json_decode($matches[0], true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON in AI response');
        }

        return $decoded;
    }
}
