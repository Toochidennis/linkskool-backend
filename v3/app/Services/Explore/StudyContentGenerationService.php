<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\Logger;
use V3\App\Models\Explore\StudyTopic;
use V3\App\Models\Explore\StudySubTopic;

class StudyContentGenerationService
{
    private StudyTopic $model;
    private StudySubTopic $subTopicModel;

    private const BATCH_SIZE   = 10;
    private const MAX_RETRIES  = 3;
    private const API_DELAY_MS = 800;

    private float $lastCall = 0;

    public function __construct(\PDO $pdo)
    {
        $this->model  = new StudyTopic($pdo);
        $this->subTopicModel  = new StudySubTopic($pdo);
    }

    public function process(int $limit = 50): void
    {
        $processed = 0;

        while ($processed < $limit) {
            $topics = $this->fetchBatch(min(self::BATCH_SIZE, $limit - $processed));

            if (empty($topics)) {
                break;
            }

            foreach ($topics as $topic) {
                $this->processSingle($topic);
                $processed++;
            }
        }
    }

    private function fetchBatch(int $limit): array
    {
        $rows = $this->model
            ->select(['id', 'title'])
            ->where('content_status', '=', 'pending')
            ->limit($limit)
            ->get();

        return array_map(function ($row) {
            $topicId   = (int) $row['id'];
            $subTopics = $this->subTopicModel
                ->select(['id', 'title', 'sub_subtopics'])
                ->where('topic_id', '=', $topicId)
                ->get();

            $subtopics = array_map(fn($st) => [
                'id'            => (int) $st['id'],
                'title'         => $st['title'],
                'sub_subtopics' => json_decode($st['sub_subtopics'], true) ?? []
            ], $subTopics);

            return [
                'id'        => $topicId,
                'title'     => $row['title'],
                'subtopics' => $subtopics
            ];
        }, $rows);
    }

    private function processSingle(array $topic): void
    {
        $id    = $topic['id'];
        $title = $topic['title'];

        try {
            $this->markProcessing($id);
            Logger::info("[study:{$id}] Starting generation", ['title' => $title]);

            $payloadHash = $this->generateHash($topic);

            if ($this->alreadyGenerated($id, $payloadHash)) {
                Logger::info("[study:{$id}] Skipped — content unchanged", ['title' => $title]);
                return;
            }

            Logger::info("[study:{$id}] Stage 1 — building structure");
            $structure    = $this->runStage1($topic);
            $sectionCount = count($structure['sections']);
            Logger::info("[study:{$id}] Stage 1 complete", ['sections' => $sectionCount]);

            Logger::info("[study:{$id}] Stage 2 — generating subsection content for {$sectionCount} sections");
            $sections = $this->runStage2($topic['title'], $structure['sections']);
            Logger::info("[study:{$id}] Stage 2 complete");

            Logger::info("[study:{$id}] Stage 3 — generating quizzes");
            $quizData = $this->runStage3($topic['title'], $sections);
            Logger::info("[study:{$id}] Stage 3 complete");

            $merged    = $this->merge($structure, $sections, $quizData);
            $validated = $this->validateAndNormalize($merged);

            $this->save($id, $validated, $payloadHash);
            Logger::info("[study:{$id}] Saved successfully", ['title' => $title]);
        } catch (\Throwable $e) {
            $this->markFailed($id, $e->getMessage());
        }
    }

    // ─── Stage 1: Structure ────────────────────────────────────────────────────

    private function runStage1(array $topic): array
    {
        $subtopicTitles = array_map(fn($st) => $st['title'], $topic['subtopics']);

        $subSubtopics = [];
        foreach ($topic['subtopics'] as $st) {
            $subSubtopics[$st['title']] = $st['sub_subtopics'];
        }

        $subtopicsJson    = json_encode($subtopicTitles, JSON_PRETTY_PRINT);
        $subSubtopicsJson = json_encode($subSubtopics, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
        You are a lesson structure generator.

        TOPIC: {$topic['title']}

        SUBTOPICS:
        {$subtopicsJson}

        SUB-SUBTOPICS (keyed by subtopic title):
        {$subSubtopicsJson}

        TASK:
        Generate ONLY the lesson skeleton. No content. No quizzes.

        Rules:
        - Each SUBTOPIC becomes a section (mark "added_by_ai": false)
        - Each SUB-SUBTOPIC becomes a subsection stub under its parent section
        - Assign sequential section IDs: section_1, section_2, ...
        - Assign subsection IDs as: 1.1, 1.2, 2.1, 2.2, ... (section_number.subsection_number)
        - Include one educational video from a reputable source (Khan Academy, CrashCourse, TED-Ed, MIT OpenCourseWare, etc.)
        - Video "placement" must reference one of the section IDs

        Return ONLY valid JSON matching this schema:
        {
          "meta": {
            "topic": "...",
            "structure": "topic → subtopic → sub-subtopic",
            "target_age": 15,
            "tone": "clear and structured",
            "difficulty": "standard",
            "has_equations": true,
            "has_worked_examples": true,
            "has_diagrams": true,
            "has_video": true,
            "sections_count": 0
          },
          "video": {
            "title": "...",
            "url": "...",
            "provider": "YouTube",
            "duration": "...",
            "placement": "section_1"
          },
          "sections": [
            {
              "id": "section_1",
              "title": "...",
              "added_by_ai": false,
              "subsections": [
                {"id": "1.1", "title": "..."},
                {"id": "1.2", "title": "..."}
              ]
            }
          ]
        }

        Return JSON only. No markdown. No commentary. No trailing commas.
        PROMPT;

        $this->rateLimit();

        return $this->callAI([
            'model'       => 'deepseek-chat',
            'messages'    => [['role' => 'user', 'content' => $prompt, 'response_format' => ['type' => 'json']]],
            'temperature' => 0.3,
            'max_tokens'  => 2000
        ]);
    }

    // ─── Stage 2: Content per section ─────────────────────────────────────────

    private function runStage2(string $topicTitle, array $sectionList): array
    {
        $results = [];

        foreach ($sectionList as $section) {
            Logger::info("[study] Section content — {$section['id']}: {$section['title']}");
            $results[$section['id']] = $this->generateSectionContent($topicTitle, $section);
        }

        return $results;
    }

    private function generateSectionContent(string $topicTitle, array $section): array
    {
        $sectionId       = $section['id'];
        $sectionTitle    = $section['title'];
        $subsectionsJson = json_encode($section['subsections'], JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
        You are generating full content for ONE section of a mobile microlesson.

        TOPIC: {$topicTitle}
        SECTION ID: {$sectionId}
        SECTION TITLE: {$sectionTitle}

        SUBSECTIONS TO FILL:
        {$subsectionsJson}

        TASK:
        Generate complete content for every subsection listed above. No quizzes.

        CONTENT BLOCK TYPES (use whichever apply per subsection):
        - Text:  {"label": "Definition", "type": "text", "text": "..."}
        - List:  {"label": "Key Points", "type": "list", "items": ["...", "..."]}
        - Pairs: {"label": "Comparison", "type": "pairs", "items": [{"term": "...", "description": "..."}]}

        AVAILABLE HEADINGS (choose only relevant ones):
        Definition, Key Idea, Explanation, Example, Concept Summary, Background, Terminology,
        Parts, Components, Structure, Types, Classification, Categories, Steps, Procedure,
        How it Works, Process Flow, Mechanism, Difference, Similarities, Comparison,
        Advantages, Disadvantages, When to Use, Formula, Equation, Symbol Meaning, Units,
        Relationship, Graph Interpretation, Worked Example, Calculation Steps, Substitution Step,
        Final Answer, Check Step, Interpretation, Uses, Applications, Real-life Example,
        Industry Use, Why it Matters, Key Points, Memory Tip, Mnemonic, Common Mistakes,
        Misconception Alert, Cause, Effect, Limitations, Diagram, Visual Summary

        EQUATION (when applicable, else null):
        {"format": "latex", "expression": "F = ma", "explanation": "...", "symbols": ["F = force", "m = mass"]}

        WORKED EXAMPLE: Include step-by-step when calculations are involved, else null.

        RULES:
        - Target: 15-year-old learner
        - Each subsection focuses on ONE concept only
        - Use consistent terminology across subsections
        - Keep explanations concise and mobile-friendly
        - Use lists wherever possible for readability

        Return ONLY valid JSON:
        {
          "section_id": "{$sectionId}",
          "subsections": [
            {
              "id": "...",
              "title": "...",
              "content": [...],
              "equation": null,
              "worked_example": null,
              "diagram_needed": false
            }
          ]
        }

        Return JSON only. No markdown. No commentary. No trailing commas.
        PROMPT;

        $this->rateLimit();

        return $this->callAI([
            'model'       => 'deepseek-chat',
            'messages'    => [['role' => 'user', 'content' => $prompt, 'response_format' => ['type' => 'json']]],
            'temperature' => 0.3,
            'max_tokens'  => 8000
        ]);
    }

    // ─── Stage 3: Quiz generation ──────────────────────────────────────────────

    private function runStage3(string $topicTitle, array $sections): array
    {
        $sectionQuizzes = [];
        $total = count($sections);
        $i     = 0;

        foreach ($sections as $sectionId => $sectionContent) {
            $i++;
            Logger::info("[study] Section quiz {$i}/{$total} — {$sectionId}");
            $sectionQuizzes[] = [
                'section_id' => $sectionId,
                'questions'  => $this->generateSectionQuiz($topicTitle, $sectionId, $sectionContent)
            ];
        }

        Logger::info("[study] Final quiz — generating 30 questions");
        $finalQuiz = $this->generateFinalQuiz($topicTitle, $sections);

        return [
            'section_quizzes' => $sectionQuizzes,
            'final_quiz'      => $finalQuiz
        ];
    }

    private function generateSectionQuiz(string $topicTitle, string $sectionId, array $section): array
    {
        $sectionJson = json_encode($section, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
        You are generating exactly 3 MCQs for one section of a mobile lesson.

        TOPIC: {$topicTitle}
        SECTION ID: {$sectionId}

        SECTION CONTENT (all subsections):
        {$sectionJson}

        TASK:
        Generate exactly 3 MCQs covering concepts across the subsections above.
        Distribution: 1 Remember + 1 Understand + 1 Apply

        MCQ FORMAT:
        {
          "id": "{$sectionId}_q1",
          "level": "Remember",
          "question": "...",
          "options": ["A. ...", "B. ...", "C. ...", "D. ..."],
          "answer": "A",
          "explanation": "..."
        }

        Rules:
        - Questions MUST reference actual content from this section only
        - No hallucinated or out-of-scope concepts
        - Language appropriate for a 15-year-old learner

        Return ONLY valid JSON:
        {
          "questions": [{...}, {...}, {...}]
        }

        Return JSON only. No markdown. No commentary. No trailing commas.
        PROMPT;

        $this->rateLimit();

        $result = $this->callAI([
            'model'       => 'deepseek-chat',
            'messages'    => [['role' => 'user', 'content' => $prompt, 'response_format' => ['type' => 'json']]],
            'temperature' => 0.3,
            'max_tokens'  => 1500
        ]);

        return $result['questions'] ?? [];
    }

    private function generateFinalQuiz(string $topicTitle, array $sections): array
    {
        $summary = $this->buildContentSummary($sections);

        $prompt = <<<PROMPT
        You are generating a 30-question final quiz for a complete mobile lesson.

        TOPIC: {$topicTitle}

        LESSON CONTENT SUMMARY (key points per section):
        {$summary}

        TASK:
        Generate exactly 30 MCQs covering the full lesson.

        BLOOM'S TAXONOMY DISTRIBUTION (must total exactly 30):
        5 Remember, 5 Understand, 5 Apply, 5 Analyse, 5 Evaluate, 5 Create

        MCQ FORMAT:
        {
          "id": "final_q1",
          "level": "Remember",
          "question": "...",
          "options": ["A. ...", "B. ...", "C. ...", "D. ..."],
          "answer": "A",
          "explanation": "..."
        }

        Rules:
        - Questions MUST be grounded in the lesson content above
        - No hallucinated concepts
        - Spread questions across all sections
        - Language appropriate for a 15-year-old learner

        Return ONLY valid JSON:
        {
          "final_quiz": [...]
        }

        Return JSON only. No markdown. No commentary. No trailing commas.
        PROMPT;

        $this->rateLimit();

        $result = $this->callAI([
            'model'  => 'deepseek-chat',
            'messages'    => [['role' => 'user', 'content' => $prompt, 'response_format' => ['type' => 'json']]],
            'temperature' => 0.3,
            'max_tokens'  => 8000
        ]);

        return $result['final_quiz'] ?? [];
    }

    private function buildContentSummary(array $sections): string
    {
        $summary = [];

        foreach ($sections as $sectionId => $section) {
            $points = [];

            foreach ($section['subsections'] ?? [] as $subsection) {
                foreach ($subsection['content'] ?? [] as $block) {
                    if ($block['type'] === 'text') {
                        $points[] = $subsection['title'] . ' — ' . $block['label'] . ': ' . $block['text'];
                    } elseif ($block['type'] === 'list') {
                        $points[] = $subsection['title'] . ' — ' . $block['label'] . ': ' . implode(', ', array_slice($block['items'], 0, 3));
                    }
                }
            }

            $summary[] = ['id' => $sectionId, 'points' => $points];
        }

        return json_encode($summary, JSON_PRETTY_PRINT);
    }

    // ─── Merge ─────────────────────────────────────────────────────────────────

    private function merge(array $structure, array $sections, array $quizData): array
    {
        $sectionQuizMap = [];
        foreach ($quizData['section_quizzes'] as $sq) {
            $sectionQuizMap[$sq['section_id']] = $sq['questions'];
        }

        $mergedSections = [];
        foreach ($structure['sections'] as $sectionMeta) {
            $id      = $sectionMeta['id'];
            $content = $sections[$id] ?? [];

            $mergedSections[] = [
                'id'  => $id,
                'title'  => $sectionMeta['title'],
                'added_by_ai'  => $sectionMeta['added_by_ai'],
                'subsections'  => $content['subsections'] ?? [],
                'quiz'  => $sectionQuizMap[$id] ?? []
            ];
        }

        return [
            'meta'  => $structure['meta'],
            'video'  => $structure['video'] ?? null,
            'sections'  => $mergedSections,
            'end_lesson_quiz'  => $quizData['final_quiz']
        ];
    }

    // ─── Infrastructure ────────────────────────────────────────────────────────

    private function rateLimit(): void
    {
        $elapsed = (microtime(true) - $this->lastCall) * 1000;

        if ($elapsed < self::API_DELAY_MS) {
            usleep((int) ((self::API_DELAY_MS - $elapsed) * 1000));
        }

        $this->lastCall = microtime(true);
    }

    private function callAI(array $payload, int $retry = 0): array
    {
        try {
            $ch = curl_init(getenv('DEEP_SEEK_URL'));

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . getenv('DEEP_SEEK_API_KEY'),
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT    => 120
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $decoded = json_decode($response, true);
            $content = $decoded['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                throw new \RuntimeException('Invalid AI response');
            }

            return $this->extractJson($content);
        } catch (\Throwable $e) {
            if ($retry < self::MAX_RETRIES) {
                usleep(1000 * (2 ** $retry));
                return $this->callAI($payload, $retry + 1);
            }

            throw $e;
        }
    }

    private function extractJson(string $content): array
    {
        if (!preg_match('/\{.*\}/s', $content, $matches)) {
            throw new \RuntimeException('No JSON found in AI response');
        }

        $decoded = json_decode($matches[0], true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON in AI response');
        }

        return $decoded;
    }

    private function validateAndNormalize(array $data): array
    {
        if (!isset($data['sections'], $data['meta'])) {
            throw new \RuntimeException('Invalid structure: missing sections or meta');
        }

        if (count($data['sections']) > 50) {
            throw new \RuntimeException('Too many sections');
        }

        return $data;
    }

    private function save(int $id, array $content, string $hash): void
    {
        $this->model
            ->where('id', $id)
            ->update([
                'content_json' => json_encode($content),
                'content_status' => 'completed',
                'content_hash' => $hash,
                'last_generated_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    private function generateHash(array $topic): string
    {
        return hash('sha256', json_encode([
            $topic['title'],
            $topic['subtopics']
        ]));
    }

    private function alreadyGenerated(int $id, string $hash): bool
    {
        $row = $this->model
            ->select(['content_hash'])
            ->where('id', $id)
            ->first();

        return $row && $row['content_hash'] === $hash;
    }

    private function markProcessing(int $id): void
    {
        $this->model->where('id', $id)->update([
            'content_status' => 'processing'
        ]);
    }

    private function markFailed(int $id, string $error): void
    {
        $this->model->where('id', $id)->update([
            'content_status' => 'failed'
        ]);

        Logger::info("[study:{$id}] FAILED — {$error}");
    }

    public function getGeneratedContent(): array
    {
        $row = $this->model->first();

        return [
            'id'  => $row['id'],
            'title'  => $row['title'],
            'content_status' => $row['content_status'],
            'content_json'   => json_decode($row['content_json'], true)
        ];
    }
}
