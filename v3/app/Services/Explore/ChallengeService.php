<?php

namespace V3\App\Services\Explore;

use DateTime;
use V3\App\Common\Enums\QuestionType;
use V3\App\Models\Explore\Challenge;
use V3\App\Models\Explore\Exam;
use V3\App\Models\Portal\ELearning\Quiz;

class ChallengeService
{
    private Challenge $challenge;
    private Exam $exam;
    private Quiz $quiz;

    public function __construct(\PDO $pdo)
    {
        $this->challenge = new Challenge($pdo);
        $this->exam = new Exam($pdo);
        $this->quiz = new Quiz($pdo);
    }

    public function createChallenge(array $data): bool
    {
        $questionIds = $this->getQuestionIds(
            $data['exam_ids'],
            $data['count_per_question']
        );

        if (empty($questionIds)) {
            return false;
        }

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'time_limit' => $data['time_limit'],
            'count_per_question' => $data['count_per_question'],
            'exam_ids' => json_encode($data['exam_ids']),
            'exam_type_id' => $data['exam_type_id'],
            'status' => $data['status'],
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'score' => $data['score'],
            'url' => json_encode($questionIds),
        ];

        return $this->challenge->insert($payload);
    }

    private function getQuestionIds(array $examIds, int $countPerQuestion): array
    {
        $url = $this->exam
            ->select(['id', 'url', 'course_id'])
            ->in('id', $examIds)
            ->get();

        if (empty($url)) {
            return [];
        }

        $selectedQuestionIds = [];

        foreach ($url as $exam) {
            $questionIds = json_decode($exam['url'], true);

            shuffle($questionIds);
            $selectedIds = \array_slice($questionIds, 0, $countPerQuestion);
            $selectedQuestionIds[$exam['id']] = $selectedIds;
        }
        return $selectedQuestionIds;
    }

    public function getChallenges(int $authorId): array
    {
        $challenges = $this->challenge
            ->select([
                'id',
                'title',
                'description',
                'start_date',
                'end_date',
                'time_limit',
                'exam_ids',
                'exam_type_id',
                'status',
                'author_id',
                'author_name',
                'score',
                'is_active',
                'created_at',
                'COUNT(challenge_participants.id) AS challengers'
            ])
            ->join('challenge_participants', 'challenge_participants.challenge_id', 'LEFT')
            ->groupBy('challenge.id')
            ->orderBy('created_at', 'DESC')
            ->get();

        $now = new DateTime();
        $grouped = [
            'recommended' => [],
            'personal' => [],
            'active' => [],
            'upcoming' => []
        ];

        foreach ($challenges as &$challenge) {
            $start = new DateTime($challenge['start_date']);
            $end = new DateTime($challenge['end_date']);
            $challenge['exam_ids'] = json_decode($challenge['exam_ids'], true);

            // Auto-update expired challenges
            if ($challenge['is_active'] == 1 && $end < $now) {
                $challenge['is_active'] = 0;
                $this->challenge
                    ->where('id', $challenge['id'])
                    ->update(['is_active' => 0]);
            }

            // Determine visibility
            $isOwner = $challenge['author_id'] == $authorId;
            $isActive = $challenge['is_active'] == 1;

            if (!$isOwner && (!$isActive || $challenge['status'] != 1)) {
                // Public should not see inactive/unpublished challenges
                continue;
            }

            if ($challenge['author_name'] === 'admin') {
                $grouped['recommended'][] = $challenge;
            } elseif ($isOwner) {
                $grouped['personal'][] = $challenge;
            } elseif ($isActive && $start <= $now && $end >= $now) {
                $grouped['active'][] = $challenge;
            } elseif ($start > $now) {
                $grouped['upcoming'][] = $challenge;
            }
        }

        return $grouped;
    }

    public function getChallengeQuestions(array $filters): array
    {
        $challenge = $this->challenge
            ->select(['id', 'url'])
            ->where('id', $filters['challenge_id'])
            ->first();

        if (!$challenge) {
            return [];
        }

        $url = $this->decode($challenge['url']);
        $questionIds = $url[$filters['exam_id']] ?? [];

        if (empty($questionIds)) {
            return [];
        }

        shuffle($questionIds);

        return $this->getQuestions($questionIds);
    }

    public function getQuestions(array $questionIds): array
    {

        $questions = $this->quiz
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
            ])
            ->in('question_id', $questionIds)
            ->get();

        if (empty($questions)) {
            return [];
        }

        $questions = array_map(function ($question) {
            $question['question_type'] = QuestionType::tryFrom($question['question_type'])?->label() ?? 'Unknown';
            $question['question_files'] = $this->decode($question['question_files']);
            $question['options'] = $this->decode($question['options']);
            $question['correct'] = $this->decode($question['correct']);
            return $question;
        }, $questions);

        return $questions;
    }

    private function decode(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return \is_array($decoded) ? $decoded : [];
    }
}
