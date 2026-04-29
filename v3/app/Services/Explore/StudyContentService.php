<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\StudyTopicExamType;

class StudyContentService
{
    protected StudyTopicExamType $studyTopicExamType;

    public function __construct(\PDO $pdo)
    {
        $this->studyTopicExamType = new StudyTopicExamType($pdo);
    }

    public function getStudyTopics(int $examTypeId, int $courseId): array
    {
        $topics = $this->studyTopicExamType->rawQuery(
            'SELECT
                t.id AS topic_id,
                t.title AS topic_name,
                t.category_id AS category_id,
                c.title AS category_name
             FROM study_topic_exam_types ste
             INNER JOIN study_topics t ON ste.topic_id = t.id
             LEFT JOIN study_categories c ON t.category_id = c.id
             WHERE ste.exam_type_id = ?
               AND ste.course_id = ?
             ORDER BY ste.display_order ASC, c.title ASC, t.title ASC',
            [$examTypeId, $courseId]
        );

        $grouped = [];

        foreach ($topics as $row) {
            $categoryId  = $row['category_id'];
            $groupKey    = $categoryId ?? 'General';

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'category_id'   => $categoryId,
                    'category_name' => $row['category_name'] ?? null,
                    'topics'        => [],
                ];
            }

            $grouped[$groupKey]['topics'][] = [
                'topic_id'   => $row['topic_id'],
                'topic_name' => $row['topic_name'],
            ];
        }

        return array_values($grouped);
    }

    public function getTopicContentByTopicId(int $topicId): ?array
    {
        $topic = $this->studyTopicExamType->rawQuery(
            'SELECT
                t.id AS topic_id,
                t.title AS topic_name,
                t.content_json,
                t.course_id,
                t.course_name,
                t.category_id,
                c.title AS category_name
             FROM study_topics t
             LEFT JOIN study_categories c ON t.category_id = c.id
             WHERE t.id = ?',
            [$topicId]
        );

        if (empty($topic)) {
            return null;
        }

        $row     = $topic[0];
        $decoded = json_decode($row['content_json'] ?? '', true);
        $content = !empty($decoded) ? $this->transformContent($decoded) : [];

        return [
            'topic_id'      => $row['topic_id'],
            'topic_name'    => $row['topic_name'],
            'course_id'     => $row['course_id'],
            'course_name'   => $row['course_name'],
            'category_id'   => $row['category_id'],
            'category_name' => $row['category_name'] ?? null,
            'topic_content' => $content,
        ];
    }

    private function transformContent(array $content): array
    {
        $out = [];

        if (!empty($content['video']) && \is_array($content['video'])) {
            $v = $content['video'];
            $out['media'] = [
                'video' => [
                    'type'      => 'video',
                    'title'     => $v['title']     ?? '',
                    'url'       => $v['url']       ?? '',
                    'provider'  => $v['provider']  ?? '',
                    'duration'  => $v['duration']  ?? '',
                    'placement' => $v['placement'] ?? null,
                ],
            ];
        }

        $out['contents'] = [];
        foreach (array_values($content['sections'] ?? []) as $i => $section) {
            $out['contents'][] = $this->transformSection($section, $i + 1);
        }

        $out['final_quiz'] = [];
        foreach (array_values($content['end_lesson_quiz'] ?? []) as $i => $q) {
            $out['final_quiz'][] = $this->transformQuiz($q, $i + 1);
        }

        return $out;
    }

    private function transformSection(array $section, int $sectionId): array
    {
        $subsections = [];
        foreach (array_values($section['subsections'] ?? []) as $i => $sub) {
            $subsections[] = $this->transformSubsection($sub, $i + 1);
        }

        $quiz = [];
        foreach (array_values($section['quiz'] ?? []) as $i => $q) {
            $quiz[] = $this->transformQuiz($q, $i + 1);
        }

        return [
            'id'          => $sectionId,
            'title'       => $section['title'] ?? '',
            'subsections' => $subsections,
            'quiz'        => $quiz,
        ];
    }

    private function transformSubsection(array $sub, int $subId): array
    {
        $cards = [];

        foreach ($sub['content'] ?? [] as $block) {
            $card = $this->transformCard($block);
            if ($card !== null) {
                $cards[] = $card;
            }
        }

        if (!empty($sub['equation'])) {
            $cards[] = [
                'type'  => 'equation',
                'title' => 'Equation',
                'body'  => \is_array($sub['equation'])
                    ? json_encode($sub['equation'])
                    : (string) $sub['equation'],
            ];
        }

        if (!empty($sub['worked_example'])) {
            $cards[] = [
                'type'  => 'worked_example',
                'title' => 'Worked Example',
                'body'  => \is_array($sub['worked_example'])
                    ? json_encode($sub['worked_example'])
                    : (string) $sub['worked_example'],
            ];
        }

        return [
            'id'             => $subId,
            'title'          => $sub['title'] ?? '',
            'cards'          => $cards,
            'media'          => [],
            'diagram_needed' => (bool) ($sub['diagram_needed'] ?? false),
        ];
    }

    private function transformCard(array $block): ?array
    {
        $type  = $block['type']  ?? null;
        $label = $block['label'] ?? '';

        switch ($type) {
            case 'text':
                $normalized  = strtolower(trim($label));
                $isHighlight = \in_array($normalized, ['key idea', 'why it matters'], true);
                return [
                    'type'  => $isHighlight ? 'highlight' : 'text',
                    'title' => $label,
                    'body'  => (string) ($block['text'] ?? ''),
                ];

            case 'list':
                return [
                    'type'  => 'list',
                    'title' => $label,
                    'items' => array_values($block['items'] ?? []),
                ];

            case 'pairs':
                $items = [];
                foreach ($block['items'] ?? [] as $pair) {
                    $items[] = [
                        'term'        => (string) ($pair['term']        ?? ''),
                        'description' => (string) ($pair['description'] ?? ''),
                    ];
                }
                return [
                    'type'  => 'pairs',
                    'title' => $label,
                    'items' => $items,
                ];
        }

        return null;
    }

    private function transformQuiz(array $q, int $id): array
    {
        $options = [];
        foreach ($q['options'] ?? [] as $opt) {
            $options[] = ['text' => $this->stripOptionPrefix((string) $opt)];
        }

        return [
            'id'             => $id,
            'question_text'  => (string) ($q['question']    ?? ''),
            'options'        => $options,
            'correct_answer' => $this->answerToIndex($q['answer'] ?? null, \count($options)),
            'explanation'    => (string) ($q['explanation'] ?? ''),
            'bloom_level'    => strtolower((string) ($q['level'] ?? '')),
        ];
    }

    private function stripOptionPrefix(string $text): string
    {
        return preg_replace('/^\s*[A-Za-z]\s*[\.\)\:\-]\s*/', '', $text) ?? $text;
    }

    private function answerToIndex(mixed $answer, int $optionCount): int
    {
        if (\is_string($answer) && $answer !== '') {
            $trimmed = trim($answer);

            if (ctype_digit($trimmed)) {
                $index = (int) $trimmed > 0 ? (int) $trimmed - 1 : 0;
                return $this->clampIndex($index, $optionCount);
            }

            $letter = strtoupper($trimmed[0]);
            if ($letter >= 'A' && $letter <= 'Z') {
                return $this->clampIndex(\ord($letter) - \ord('A'), $optionCount);
            }
        }

        if (\is_int($answer)) {
            return $this->clampIndex($answer, $optionCount);
        }

        return 0;
    }

    private function clampIndex(int $index, int $optionCount): int
    {
        if ($index < 0) {
            return 0;
        }
        if ($optionCount > 0 && $index >= $optionCount) {
            return $optionCount - 1;
        }
        return $index;
    }
}
