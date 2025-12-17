<?php

namespace V3\App\Services\Explore;

use PDO;
use V3\App\Models\Explore\Syllabi;
use V3\App\Models\Explore\Topic;
use V3\App\Models\Portal\ELearning\Quiz;

class TopicService
{
    private Syllabi $syllabi;
    private Topic $topic;
    private Quiz $quiz;
    private PDO $pdo;
    private array $topicCache = [];
    private array $syllabusCache = [];
    private int $apiCallCount = 0;
    private float $lastApiCallTime = 0;

    // Configuration for rate limiting
    private const BATCH_SIZE = 100;
    private const API_DELAY_MS = 500; // 500ms delay between API calls
    private const MAX_RETRIES = 3;
    private const RETRY_BACKOFF_MS = 1000;

    public function __construct(PDO $pdo)
    {
        $this->syllabi = new Syllabi($pdo);
        $this->topic = new Topic($pdo);
        $this->quiz = new Quiz($pdo);
        $this->pdo = $pdo;
    }

    public function getSyllabusAndTopics(int $courseId, int $examTypeId): array
    {
        $rows = $this->syllabi
            ->select([
                'syllabi.id AS syllabus_id',
                'syllabi.name AS syllabus_name',
                'topics.id AS topic_id',
                'topics.name AS topic_name'
            ])
            ->join('topics', 'topics.syllabus_id = syllabi.id', 'LEFT')
            ->join('question_table', 'question_table.topic_id = topics.id', 'LEFT')
            ->where('syllabi.course_id', '=', $courseId)
            ->where('question_table.exam_type', '=', $examTypeId)
            ->orderBy('syllabi.name')
            ->orderBy('topics.name')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $sid = $row['syllabus_id'];

            if (!isset($grouped[$sid])) {
                $grouped[$sid] = [
                    'syllabus_id' => $sid,
                    'syllabus_name' => $row['syllabus_name'],
                    'topics' => []
                ];
            }

            if (!empty($row['topic_id'])) {
                // Deduplicate topics per syllabus
                $grouped[$sid]['topics'][$row['topic_id']] = [
                    'topic_id' => $row['topic_id'],
                    'topic_name' => $row['topic_name'],
                ];
            }
        }

        // Normalize topics to indexed arrays
        foreach ($grouped as &$syllabus) {
            $syllabus['topics'] = array_values($syllabus['topics']);
        }
        unset($syllabus);

        return array_values($grouped);
    }

    public function processQuestions(int $limit = 10): bool
    {
        $processed = 0;
        $offset = 0;
        $totalLimit = $limit;

        // Process in batches to avoid overwhelming DB and API
        while ($processed < $totalLimit) {
            $batchLimit = min(self::BATCH_SIZE, $totalLimit - $processed);
            $questions = $this->fetchUnprocessedQuestions($batchLimit, $offset);

            if (empty($questions)) {
                break; // No more questions to process
            }

            // Process entire batch in single transaction
            try {
                $this->pdo->beginTransaction();

                foreach ($questions as $question) {
                    $this->processQuestion($question);
                }

                $this->pdo->commit();
                $processed += \count($questions);
                $offset += \count($questions);
            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                throw $e;
            }
        }

        return true;
    }

    private function processQuestion(array $question): void
    {
        try {
            // Step 1: Fetch existing syllabi for this course
            $existingSyllabi = $this->getExistingSyllabi($question['course_id']);

            // Step 2: Ask AI to pick existing syllabus or suggest new one
            $syllabusResult = $this->askAIForSyllabus([
                'existing_syllabi' => $existingSyllabi,
                'course_name' => $question['course_name'],
                'question_text' => $question['question_text'],
                'passage' => $question['passage'],
                'instruction' => $question['instruction'],
            ]);

            // Step 3: Get or create syllabus
            if ($syllabusResult['type'] === 'existing') {
                $syllabusId = $syllabusResult['syllabus_id'];
            } else {
                // AI suggested a new syllabus
                $syllabusId = $this->insertSyllabus([
                    'name' => $syllabusResult['name'],
                    'normalized_name' => $this->normalize($syllabusResult['name']),
                    'course_id' => $question['course_id']
                ]);
            }

            if ($syllabusId <= 0) {
                throw new \RuntimeException('Failed to get or create syllabus');
            }

            // Step 4: Fetch existing topics for this syllabus
            $existingTopics = $this->getExistingTopics($syllabusId);

            // Step 5: Ask AI to pick existing topic or suggest new one
            $topicResult = $this->askAIForTopic([
                'existing_topics' => $existingTopics,
                'course_name' => $question['course_name'],
                'syllabus_name' => $syllabusResult['name'] ?? ($this->getSyllabusName($syllabusId)),
                'question_text' => $question['question_text'],
                'passage' => $question['passage'],
                'instruction' => $question['instruction'],
            ]);

            // Step 6: Get or create topic
            if ($topicResult['type'] === 'existing') {
                $topicId = $topicResult['topic_id'];
                $topicName = $topicResult['name'];
            } else {
                // AI suggested a new topic
                $topicId = $this->insertTopic([
                    'name' => $topicResult['name'],
                    'syllabus_id' => $syllabusId,
                    'normalized_name' => $this->normalize($topicResult['name']),
                    'course_id' => $question['course_id']
                ]);
                $topicName = $topicResult['name'];
            }

            if ($topicId <= 0) {
                throw new \RuntimeException('Failed to get or create topic');
            }

            // Step 7: Update question with topic info
            $updated = $this->quiz
                ->where('question_id', '=', $question['question_id'])
                ->update([
                    'topic_id' => $topicId,
                    'topic' => $topicName,
                    'topic_status' => 'processed'
                ]);

            if (!$updated) {
                throw new \RuntimeException('Failed to update question with topic ID');
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    private function fetchUnprocessedQuestions($limit, $offset = 0): array
    {
        return $this->quiz
            ->select([
                'question_id',
                'title AS question_text',
                'passage',
                'instruction',
                'course_name',
                'course_id'
            ])
            ->where('topic_status', '=', 'pending')
            ->orderBy('question_id')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    private function insertSyllabus(array $data): int
    {
        $normalized = $data['normalized_name'];
        $courseId = $data['course_id'];

        // Check cache first
        $cacheKey = "$courseId:$normalized";
        if (isset($this->syllabusCache[$cacheKey])) {
            return $this->syllabusCache[$cacheKey];
        }

        $syllabusExists = $this->syllabi
            ->select(['id'])
            ->where('normalized_name', '=', $normalized)
            ->where('course_id', '=', $courseId)
            ->first();

        if ($syllabusExists) {
            $id = (int) $syllabusExists['id'];
            $this->syllabusCache[$cacheKey] = $id;
            return $id;
        }

        $id = (int) $this->syllabi->insert([
            'name' => $data['name'],
            'normalized_name' => $normalized,
            'course_id' => $courseId,
        ]);

        $this->syllabusCache[$cacheKey] = $id;
        return $id;
    }

    private function insertTopic(array $data): int
    {
        $normalized = $data['normalized_name'];
        $syllabusId = $data['syllabus_id'];

        // Check cache first
        $cacheKey = "$syllabusId:$normalized";
        if (isset($this->topicCache[$cacheKey])) {
            return $this->topicCache[$cacheKey];
        }

        $existing = $this->topic
            ->select(['id'])
            ->where('normalized_name', '=', $normalized)
            ->where('syllabus_id', '=', $syllabusId)
            ->first();

        if ($existing) {
            $id = (int) $existing['id'];
            $this->topicCache[$cacheKey] = $id;
            return $id;
        }

        $id = (int) $this->topic->insert([
            'name' => $data['name'],
            'normalized_name' => $normalized,
            'syllabus_id' => $syllabusId,
            'course_id' => $data['course_id'],
        ]);

        $this->topicCache[$cacheKey] = $id;
        return $id;
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }

    private function getExistingSyllabi(int $courseId): array
    {
        $syllabi = $this->syllabi
            ->select(['id', 'name'])
            ->where('course_id', '=', $courseId)
            ->orderBy('name')
            ->get();

        return array_map(fn($s) => ['id' => $s['id'], 'name' => $s['name']], $syllabi);
    }

    private function getExistingTopics(int $syllabusId): array
    {
        $topics = $this->topic
            ->select(['id', 'name'])
            ->where('syllabus_id', '=', $syllabusId)
            ->orderBy('name')
            ->get();

        return array_map(fn($t) => ['id' => $t['id'], 'name' => $t['name']], $topics);
    }

    private function getSyllabusName(int $syllabusId): string
    {
        $syllabus = $this->syllabi
            ->select(['name'])
            ->where('id', '=', $syllabusId)
            ->first();

        return $syllabus['name'] ?? 'Unknown';
    }

    private function askAIForSyllabus(array $data): array
    {
        // Rate limiting
        $timeSinceLastCall = (microtime(true) - $this->lastApiCallTime) * 1000;
        if ($timeSinceLastCall < self::API_DELAY_MS) {
            usleep((self::API_DELAY_MS - $timeSinceLastCall) * 1000);
        }

        $contextParts = [];

        if (!empty($data['passage'])) {
            $passage = \strlen($data['passage']) > 300
                ? substr($data['passage'], 0, 300) . '...'
                : $data['passage'];
            $contextParts[] = "Passage:\n$passage";
        }

        if (!empty($data['instruction'])) {
            $contextParts[] = "Instruction:\n{$data['instruction']}";
        }

        $contextParts[] = "Question:\n{$data['question_text']}";
        $context = implode("\n\n", $contextParts);

        // Format existing syllabi list with IDs
        $syllabiFmt = empty($data['existing_syllabi'])
            ? "None yet"
            : implode("\n", array_map(fn($s) => "- [{$s['id']}] {$s['name']}", $data['existing_syllabi']));

        $prompt = <<<PROMPT
You are an expert educational curriculum classifier. Analyze this question and assign it to a syllabus area.

EXISTING SYLLABI FOR THIS COURSE:
$syllabiFmt

DECISION:
- If the question clearly fits ONE of the existing syllabi above, respond with its exact ID and name in "type": "existing"
- If NONE fit, respond with "type": "new_syllabus" and suggest a NEW syllabus name, be precise.

Question Analysis:
Course: {$data['course_name']}

{$context}

Return ONLY valid JSON (no explanations, no markdown):
{
  "type": "existing" OR "new_syllabus",
  "name": "the syllabus name",
  "syllabus_id": 123 (ONLY if type is "existing" - use the ID from the list above)
}

Examples:
{"type": "existing", "name": "Cellular Biology", "syllabus_id": 5}
{"type": "new_syllabus", "name": "Quantum Mechanics"}
PROMPT;

        return $this->callAIWithRetry($prompt, 0, 'syllabus');
    }

    private function askAIForTopic(array $data): array
    {
        // Rate limiting
        $timeSinceLastCall = (microtime(true) - $this->lastApiCallTime) * 1000;
        if ($timeSinceLastCall < self::API_DELAY_MS) {
            usleep((self::API_DELAY_MS - $timeSinceLastCall) * 1000);
        }

        $contextParts = [];

        if (!empty($data['passage'])) {
            $passage = \strlen($data['passage']) > 300
                ? substr($data['passage'], 0, 300) . '...'
                : $data['passage'];
            $contextParts[] = "Passage:\n$passage";
        }

        if (!empty($data['instruction'])) {
            $contextParts[] = "Instruction:\n{$data['instruction']}";
        }

        $contextParts[] = "Question:\n{$data['question_text']}";
        $context = implode("\n\n", $contextParts);

        // Format existing topics list with IDs
        $topicsFmt = empty($data['existing_topics'])
            ? "None yet"
            : implode("\n", array_map(fn($t) => "- [{$t['id']}] {$t['name']}", $data['existing_topics']));

        $prompt = <<<PROMPT
You are an expert educational curriculum classifier. Analyze this question and assign it to a specific topic.

SYLLABUS: {$data['syllabus_name']}

EXISTING TOPICS IN THIS SYLLABUS:
$topicsFmt

DECISION:
- If the question clearly fits ONE of the existing topics above, respond with its exact ID and name in "type": "existing"
- If NONE fit, respond with "type": "new_topic" and suggest a NEW specific topic, be precise.

Be very specific when suggesting new topics. Avoid generic names.

Question Analysis:
Course: {$data['course_name']}

{$context}

Return ONLY valid JSON (no explanations, no markdown):
{
  "type": "existing" OR "new_topic",
  "name": "the topic name",
  "topic_id": 123 (ONLY if type is "existing" - use the ID from the list above)
}

Examples:
{"type": "existing", "name": "Mitochondrial respiration", "topic_id": 42}
{"type": "new_topic", "name": "Photosynthesis light reactions"}
PROMPT;

        return $this->callAIWithRetry($prompt, 0, 'topic');
    }

    //     private function getSyllabusAndTopicFromAI(array $data): array
    //     {
    //         // Rate limiting: enforce minimum delay between API calls
    //         $timeSinceLastCall = (microtime(true) - $this->lastApiCallTime) * 1000;
    //         if ($timeSinceLastCall < self::API_DELAY_MS) {
    //             usleep((self::API_DELAY_MS - $timeSinceLastCall) * 1000);
    //         }

    //         $contextParts = [];

    //         if (!empty($data['passage'])) {
    //             // Summarize long passages
    //             $passage = \strlen($data['passage']) > 300
    //                 ? substr($data['passage'], 0, 300) . '...'
    //                 : $data['passage'];
    //             $contextParts[] = "Passage:\n$passage";
    //         }

    //         if (!empty($data['instruction'])) {
    //             $contextParts[] = "Instruction:\n{$data['instruction']}";
    //         }

    //         $contextParts[] = "Question:\n{$data['question_text']}";

    //         $context = implode("\n\n", $contextParts);

    //         $prompt = <<<PROMPT
    // You are an expert educational curriculum classifier. Your task is to analyze exam questions and assign them to specific academic topics and syllabus areas.

    // IMPORTANT: Identify the MOST SPECIFIC topic for this question. Avoid generic topics. Look for specific concepts, skills, or knowledge areas being tested.

    // Analyze:
    // Course: {$data['course_name']}

    // {$context}

    // Return ONLY valid JSON (no explanations, no markdown, no code blocks, no numbering) in the following format:
    // {
    //   "syllabus": "curriculum area or unit (2-4 words)",
    //   "topic": "specific topic/concept being tested (3-6 words, be precise)"
    // }

    // Examples of good responses:
    // {"syllabus": "Thermodynamics", "topic": "Heat transfer and conduction"}
    // {"syllabus": "Shakespearean Literature", "topic": "Character analysis and motivation"}
    // {"syllabus": "Cellular Biology", "topic": "Mitochondrial respiration and energy production"}

    // Be specific. Avoid vague topics like "general knowledge" or "reading comprehension".
    // PROMPT;

    //         return $this->callAIWithRetry($prompt);
    //     }

    private function callAIWithRetry(string $prompt, int $retryCount = 0, string $type = 'general'): array
    {
        try {
            $payload = [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $type === 'general' ? 0.3 : 0.2,
                'max_tokens' => 200
            ];

            $ch = curl_init(getenv('DEEP_SEEK_URL'));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . getenv('DEEP_SEEK_API_KEY'),
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->lastApiCallTime = microtime(true);
            $this->apiCallCount++;

            if ($response === false) {
                throw new \RuntimeException('DeepSeek API request failed');
            }

            // Handle rate limiting (HTTP 429)
            if ($httpCode === 429) {
                if ($retryCount < self::MAX_RETRIES) {
                    $backoffMs = self::RETRY_BACKOFF_MS * pow(2, $retryCount);
                    usleep($backoffMs * 1000);
                    return $this->callAIWithRetry($prompt, $retryCount + 1, $type);
                }
                throw new \RuntimeException('DeepSeek API rate limit exceeded after retries');
            }

            if ($httpCode >= 400) {
                throw new \RuntimeException("DeepSeek API error: HTTP $httpCode");
            }

            $decoded = json_decode($response, true);

            if (!isset($decoded['choices'][0]['message']['content'])) {
                throw new \RuntimeException('Invalid DeepSeek response structure');
            }

            $content = $decoded['choices'][0]['message']['content'];

            // Extract JSON if wrapped in markdown code blocks
            if (strpos($content, '```json') !== false) {
                preg_match('/```json\s*(.*?)\s*```/s', $content, $matches);
                $content = $matches[1] ?? $content;
            } elseif (strpos($content, '```') !== false) {
                preg_match('/```\s*(.*?)\s*```/s', $content, $matches);
                $content = $matches[1] ?? $content;
            }

            $result = json_decode($content, true);

            // Validate based on type
            if ($type === 'syllabus') {
                if (!\is_array($result) || empty($result['type']) || empty($result['name'])) {
                    throw new \RuntimeException('AI returned invalid syllabus classification: ' . $content);
                }
                // Map name to match existing syllabus if type is existing
                if ($result['type'] === 'existing' && !isset($result['syllabus_id'])) {
                    // Find the ID from existing syllabi
                    throw new \RuntimeException('Missing syllabus_id for existing syllabus');
                }
            } elseif ($type === 'topic') {
                if (!\is_array($result) || empty($result['type']) || empty($result['name'])) {
                    throw new \RuntimeException('AI returned invalid topic classification: ' . $content);
                }
                // Map name to match existing topic if type is existing
                if ($result['type'] === 'existing' && !isset($result['topic_id'])) {
                    // Find the ID from existing topics
                    throw new \RuntimeException('Missing topic_id for existing topic');
                }
            } else {
                // General type
                if (!\is_array($result) || empty($result['syllabus']) || empty($result['topic'])) {
                    throw new \RuntimeException('AI returned invalid classification: ' . $content);
                }
            }

            return $result;
        } catch (\Throwable $e) {
            if ($retryCount < self::MAX_RETRIES) {
                $backoffMs = self::RETRY_BACKOFF_MS * pow(2, $retryCount);
                usleep($backoffMs * 1000);
                return $this->callAIWithRetry($prompt, $retryCount + 1, $type);
            }
            throw $e;
        }
    }
}
