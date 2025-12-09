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

    private const ADMIN_USER = 'admin';
    private const PUBLISHED = 'published';
    private const DRAFT = 'draft';
    private const ACTIVE = 'active';
    private const INACTIVE = 'inactive';

    public function __construct(\PDO $pdo)
    {
        $this->challenge = new Challenge($pdo);
        $this->exam = new Exam($pdo);
        $this->quiz = new Quiz($pdo);
    }

    /**
     * Summary of createChallenge
     * @param array $data
     * @return bool|int
     */
    public function createChallenge(array $data): bool
    {
        $questionIds = $this->getQuestionIds(
            $data['exam_ids'],
            $data['count_per_exam']
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
            'count_per_exam' => $data['count_per_exam'],
            'exam_ids' => json_encode($data['exam_ids']),
            'exam_type_id' => $data['exam_type_id'],
            'status' => $data['status'] === self::PUBLISHED ? 1 : 0,
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'score' => $data['score'],
            'url' => json_encode($questionIds),
        ];

        return $this->challenge->insert($payload);
    }

    public function updateChallenge(array $data): bool
    {
        $questionIds = $this->getQuestionIds(
            $data['exam_ids'],
            $data['count_per_exam']
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
            'count_per_exam' => $data['count_per_exam'],
            'exam_ids' => json_encode($data['exam_ids']),
            'exam_type_id' => $data['exam_type_id'],
            'status' => $data['status'] === self::PUBLISHED ? 1 : 0,
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'score' => $data['score'],
            'url' => json_encode($questionIds),
            'updated_at' => date('Y-m-d H:i:s'),
            'is_active' => 1,
        ];

        return $this->challenge
            ->where('id', $data['challenge_id'])
            ->update($payload);
    }

    public function updateStatus(int $challengeId, string $status): bool
    {
        $payload = [
            'status' => $status === self::PUBLISHED ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->challenge
            ->where('id', $challengeId)
            ->update($payload);
    }

    public function deleteChallenge(int $challengeId, int $authorId): bool
    {
        return $this->challenge
            ->where('id', '=', $challengeId)
            ->where('author_id', '=', $authorId)
            ->delete();
    }

    private function getQuestionIds(array $examIds, int $countPerExam): array
    {
        $url = $this->exam
            ->select(['id', 'url'])
            ->in('id', $examIds)
            ->get();

        if (empty($url)) {
            return [];
        }

        $selectedQuestionIds = [];

        foreach ($url as $exam) {
            $questionIds = json_decode($exam['url'], true);

            shuffle($questionIds);
            $selectedIds = \array_slice($questionIds, 0, $countPerExam);
            $selectedQuestionIds[$exam['id']] = $selectedIds;
        }
        return $selectedQuestionIds;
    }

    /**
     * Summary of getChallenges
     * @param int $authorId
     * @return array[]|array{active: array, personal: array, recommended: array, upcoming: array}
     */
    public function getChallenges(int $authorId): array
    {
        $challenges = $this->challenge
            ->select([
                'challenge.id',
                'challenge.title',
                'challenge.description',
                'challenge.start_date',
                'challenge.end_date',
                'challenge.time_limit',
                'challenge.exam_ids',
                'challenge.status',
                'challenge.author_id',
                'challenge.author_name',
                'challenge.score',
                'challenge.is_active',
                'challenge.created_at',
                'COUNT(challenge_participants.id) AS challengers'
            ])
            ->join('challenge_participants', 'challenge_participants.challenge_id = challenge.id', 'LEFT')
            ->groupBy('challenge.id')
            ->orderBy('challenge.created_at', 'DESC')
            ->get();

        // var_dump($challenges); exit;

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
            $challenge['status'] = $challenge['status'] == 1 ? self::PUBLISHED : self::DRAFT;
            $challenge['is_active'] = $challenge['is_active'] === 1 ? self::ACTIVE : self::INACTIVE;

            // Auto-update expired challenges
            if ($challenge['is_active'] == self::ACTIVE && $end < $now) {
                $challenge['is_active'] = self::INACTIVE;
                $this->challenge
                    ->where('id', $challenge['id'])
                    ->update(['is_active' => 0]);
            }

            // Determine visibility
            $isOwner = $challenge['author_id'] == $authorId;
            $isActive = $challenge['is_active'] == self::ACTIVE;

            if (!$isOwner && (!$isActive || $challenge['status'] != self::PUBLISHED)) {
                // Public should not see inactive/unpublished challenges
                continue;
            }

            if ($challenge['author_name'] === self::ADMIN_USER) {
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

    /**
     * Summary of getChallengeQuestions
     * @param array $filters
     * @return array
     */
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

    /**
     * Summary of getQuestions
     * @param array $questionIds
     * @return array
     */
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

    /**
     * Summary of decode
     * @param mixed $data
     * @return array
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
