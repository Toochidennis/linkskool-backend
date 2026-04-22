<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\Logger;
use V3\App\Models\Explore\StudyTopic;

class StudyGenerationServiceV2
{
    private StudyTopic $model;

    private const BATCH_SIZE   = 10;
    private const MAX_RETRIES  = 3;
    private const API_DELAY_MS = 800;

    private const AI_URL   = 'OPENAI_API_URL';
    private const AI_KEY   = 'OPENAI_API_KEY';
    private const AI_MODEL = 'gpt-5.4';

    private float $lastCall = 0;

    public function __construct(\PDO $pdo)
    {
        $this->model = new StudyTopic($pdo);
    }

    public function process(int $limit = 50): void
    {
        $processed = 0;

        while ($processed < $limit) {
            $topics = $this->fetchBatch(\min(self::BATCH_SIZE, $limit - $processed));

            if (empty($topics)) {
                break;
            }

            foreach ($topics as $topic) {
                $this->processSingle($topic);
                $processed++;
            }
        }
    }

    public function getGeneratedContent(): array
    {
        return $this->model
            ->select(['id', 'title', 'content_json', 'content_status', 'last_generated_at'])
            ->where('content_status', '=', 'completed')
            ->get();
    }

    private function fetchBatch(int $limit): array
    {
        $rows = $this->model
            ->select(['id', 'title', 'subtopics_json'])
            ->where('content_status', '=', 'pending')
            ->limit($limit)
            ->get();

        return array_map(fn($row) => [
            'id'        => (int) $row['id'],
            'title'     => $row['title'],
            'subtopics' => json_decode($row['subtopics_json'], true) ?? []
        ], $rows);
    }

    private function processSingle(array $topic): void
    {
        $id    = $topic['id'];
        $title = $topic['title'];

        try {
            $this->markProcessing($id);
            Logger::info("[study:{$id}] Starting generation (v2 / " . self::AI_MODEL . ")", ['title' => $title]);

            $payloadHash = $this->generateHash($topic);

            if ($this->alreadyGenerated($id, $payloadHash)) {
                Logger::info("[study:{$id}] Skipped — content unchanged", ['title' => $title]);
                return;
            }

            Logger::info("[study:{$id}] Stage 1 — building structure");
            $structure    = $this->runStage1($topic);
            $sectionCount = \count($structure['sections']);
            Logger::info("[study:{$id}] Stage 1 complete", ['sections' => $sectionCount]);

            Logger::info("[study:{$id}] Stage 2 — content + quiz for {$sectionCount} sections");
            $sections = $this->runStage2($topic['title'], $structure['sections']);
            Logger::info("[study:{$id}] Stage 2 complete");

            Logger::info("[study:{$id}] Stage 3 — final quiz");
            $finalQuiz = $this->runStage3($topic);
            Logger::info("[study:{$id}] Stage 3 complete", ['questions' => \count($finalQuiz)]);

            $merged    = $this->merge($structure, $sections, $finalQuiz);
            $validated = $this->validateAndNormalize($merged);

            $this->save($id, $validated, $payloadHash);
            Logger::info("[study:{$id}] Saved successfully", ['title' => $title]);
        } catch (\Throwable $e) {
            $this->markFailed($id, $e->getMessage());
        }
    }

    // ─── Stage 1: Structure (unchanged) ───────────────────────────────────────

    private function runStage1(array $topic): array
    {
        $subtopics = json_encode($topic['subtopics'], JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
        You are a lesson structure generator.

        TOPIC: {$topic['title']}

        SUBTOPICS:
        {$subtopics}

        TASK:
        Generate ONLY the lesson structure. No content. No quizzes.

        Rules:
        - Keep user-provided subtopics (mark "added_by_ai": false)
        - Add missing prerequisite subtopics if needed (mark "added_by_ai": true)
        - Assign sequential section IDs: section_1, section_2, ...
        - Assign a purpose per section: "concept", "process", "formula", "example", or "application"
        - Include one video from a reputable source (Khan Academy, CrashCourse, TED-Ed, MIT OpenCourseWare, etc.)
        - Video "placement" must reference one of the section IDs

        Return ONLY valid JSON matching this schema:
        {
          "meta": {
            "topic": "...",
            "subtopics_provided": true,
            "additional_subtopics_added": true,
            "target_age": 15,
            "tone": "clear and structured",
            "difficulty": "standard",
            "estimated_sections": 0,
            "has_equations": true,
            "has_worked_examples": true,
            "has_diagrams": true,
            "has_video": true,
            "equation_format": "latex",
            "subtopic_quiz_per_section": 3,
            "final_quiz_questions": 30
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
              "purpose": "concept"
            }
          ]
        }

        Return JSON only. No markdown. No commentary. No trailing commas.
        PROMPT;

        $this->rateLimit();

        return $this->callAI([
            'model'       => self::AI_MODEL,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.3,
            'max_tokens'  => 2000
        ]);
    }

    // ─── Stage 2: Content + subtopic quiz combined (one call per section) ─────

    private function runStage2(string $topicTitle, array $sectionList): array
    {
        $fullSectionList = json_encode($sectionList, JSON_PRETTY_PRINT);
        $results         = [];
        $total           = \count($sectionList);
        $i               = 0;

        foreach ($sectionList as $section) {
            $i++;
            Logger::info("[study] Section {$i}/{$total} — {$section['id']}: {$section['title']}");
            $results[$section['id']] = $this->generateSectionWithQuiz(
                $topicTitle,
                $fullSectionList,
                $section
            );
        }

        return $results;
    }

    private function generateSectionWithQuiz(string $topicTitle, string $fullSectionList, array $section): array
    {
        $sectionId    = $section['id'];
        $sectionTitle = $section['title'];

        $prompt = <<<PROMPT
        You are generating content AND 3 quiz questions for ONE section of a mobile lesson.

        TOPIC: {$topicTitle}

        FULL LESSON STRUCTURE (for terminology consistency — do NOT generate content for other sections):
        {$fullSectionList}

        CURRENT SECTION:
        ID: {$sectionId}
        Title: {$sectionTitle}

        TASK:
        Generate in a single response:
        1. Content blocks for this section
        2. Exactly 3 MCQs derived directly from the content you just wrote

        CONTENT BLOCK TYPES:
        - Text:  {"label": "Definition", "type": "text", "text": "..."}
        - List:  {"label": "Steps", "type": "list", "items": ["...", "..."]}
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

        IMAGE PROMPT: Write a flat-illustration prompt when a diagram improves understanding, else null.

        QUIZ RULES:
        - Exactly 3 MCQs: 1 Remember + 1 Understand + 1 Apply
        - Questions MUST be grounded in the content you generated above — no hallucinated concepts
        - Language appropriate for a 15-year-old learner

        MCQ FORMAT:
        {
          "id": "{$sectionId}_q1",
          "level": "Remember",
          "question": "...",
          "options": ["A. ...", "B. ...", "C. ...", "D. ..."],
          "answer": "A",
          "explanation": "..."
        }

        CONTENT RULES:
        - Target: 15-year-old learner
        - Do NOT redefine concepts already covered in earlier sections
        - Use consistent terminology across all sections
        - Keep explanations mobile-friendly and concise

        Return ONLY valid JSON:
        {
          "id": "{$sectionId}",
          "content": [...],
          "equation": null,
          "worked_example": null,
          "image_prompt": null,
          "diagram_needed": false,
          "subtopic_quiz": [
            {"id": "{$sectionId}_q1", "level": "Remember", "question": "...", "options": [...], "answer": "A", "explanation": "..."},
            {"id": "{$sectionId}_q2", "level": "Understand", "question": "...", "options": [...], "answer": "B", "explanation": "..."},
            {"id": "{$sectionId}_q3", "level": "Apply", "question": "...", "options": [...], "answer": "C", "explanation": "..."}
          ]
        }

        Return JSON only. No markdown. No commentary. No trailing commas.
        PROMPT;

        $this->rateLimit();

        return $this->callAI([
            'model'       => self::AI_MODEL,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.3,
            'max_tokens'  => 5000
        ]);
    }

    // ─── Stage 3: Final quiz from topic + subtopics only ──────────────────────

    private function runStage3(array $topic): array
    {
        $subtopics = json_encode($topic['subtopics'], JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
        You are generating a 30-question final quiz for a mobile lesson.

        TOPIC: {$topic['title']}

        SUBTOPICS:
        {$subtopics}

        TASK:
        Generate exactly 30 MCQs covering the full topic and all subtopics listed above.

        BLOOM'S TAXONOMY DISTRIBUTION (must total exactly 30):
        5 Remember, 5 Understand, 6 Apply, 6 Analyse, 4 Evaluate, 4 Create

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
        - Spread questions proportionally across all subtopics
        - No hallucinated concepts — stick to what the subtopics cover
        - Language appropriate for a 15-year-old learner
        - Each question must have a clear, factual explanation

        Return ONLY valid JSON:
        {
          "final_quiz": [...]
        }

        Return JSON only. No markdown. No commentary. No trailing commas.
        PROMPT;

        $this->rateLimit();

        $result = $this->callAI([
            'model'       => self::AI_MODEL,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.3,
            'max_tokens'  => 5000
        ]);

        return $result['final_quiz'] ?? [];
    }

    // ─── Merge ─────────────────────────────────────────────────────────────────

    private function merge(array $structure, array $sections, array $finalQuiz): array
    {
        $mergedSections = [];

        foreach ($structure['sections'] as $sectionMeta) {
            $id      = $sectionMeta['id'];
            $content = $sections[$id] ?? [];

            $mergedSections[] = [
                'id'             => $id,
                'title'          => $sectionMeta['title'],
                'added_by_ai'    => $sectionMeta['added_by_ai'],
                'purpose'        => $sectionMeta['purpose'] ?? 'concept',
                'content'        => $content['content'] ?? [],
                'equation'       => $content['equation'] ?? null,
                'worked_example' => $content['worked_example'] ?? null,
                'image_prompt'   => $content['image_prompt'] ?? null,
                'diagram_needed' => $content['diagram_needed'] ?? false,
                'subtopic_quiz'  => $content['subtopic_quiz'] ?? []
            ];
        }

        return [
            'meta'            => $structure['meta'],
            'video'           => $structure['video'] ?? null,
            'sections'        => $mergedSections,
            'end_lesson_quiz' => $finalQuiz
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
            $ch = curl_init(getenv(self::AI_URL));

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . getenv(self::AI_KEY),
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

        if (\count($data['sections']) > 50) {
            throw new \RuntimeException('Too many sections');
        }

        return $data;
    }

    private function save(int $id, array $content, string $hash): void
    {
        $this->model
            ->where('id', $id)
            ->update([
                'content_json'      => json_encode($content),
                'content_status'    => 'completed',
                'content_hash'      => $hash,
                'last_generated_at' => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
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
}
