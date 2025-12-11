<?php

namespace V3\App\Services\Explore;

use DateTime;
use V3\App\Common\Enums\ChallengeStatus;
use V3\App\Models\Explore\Challenge;
use V3\App\Models\Explore\ChallengeParticipants;
use V3\App\Models\Explore\Exam;

class ChallengeService
{
    private Challenge $challenge;
    private Exam $exam;
    private QuestionService $questionService;
    private ChallengeParticipants $challengeParticipants;

    private const ADMIN_USER = 'admin';

    public function __construct(\PDO $pdo)
    {
        $this->challenge = new Challenge($pdo);
        $this->exam = new Exam($pdo);
        $this->questionService = new QuestionService($pdo);
        $this->challengeParticipants = new ChallengeParticipants($pdo);
    }

    /**
     * Summary of createChallenge
     * @param array $data
     * @return bool|int
     */
    public function createChallenge(array $data): bool
    {
        $questionIds = $this->getQuestionIds(
            array_column($data['details'], 'exam_id'),
            $data['count_per_exam']
        );

        if (empty($questionIds)) {
            return false;
        }

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'time_limit' => $data['time_limit'],
            'count_per_exam' => $data['count_per_exam'],
            'details' => json_encode($data['details']),
            'exam_type_id' => $data['exam_type_id'],
            'status' => ChallengeStatus::fromLabel($data['status'])->value,
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'score' => $data['score'],
            'question_ids' => json_encode($questionIds),
            'published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null,
        ];

        return $this->challenge->insert($payload);
    }

    public function updateChallenge(array $data): bool
    {
        $questionIds = $this->getQuestionIds(
            array_column($data['details'], 'exam_id'),
            $data['count_per_exam']
        );

        if (empty($questionIds)) {
            return false;
        }

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'time_limit' => $data['time_limit'],
            'count_per_exam' => $data['count_per_exam'],
            'details' => json_encode($data['details']),
            'exam_type_id' => $data['exam_type_id'],
            'status' => ChallengeStatus::fromLabel($data['status'])->value,
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'score' => $data['score'],
            'question_ids' => json_encode($questionIds),
            'updated_at' => date('Y-m-d H:i:s'),
            'is_active' => 1,
            'published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null,
        ];

        return $this->challenge
            ->where('id', $data['challenge_id'])
            ->update($payload);
    }

    public function updateStatus(int $challengeId, string $status): bool
    {
        $payload = [
            'status' => ChallengeStatus::fromLabel($status)->value,
            'updated_at' => date('Y-m-d H:i:s'),
            'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
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
    public function getChallenges(array $filters): array
    {
        $challenges = $this->challenge
            ->select([
                'challenge.id',
                'challenge.title',
                'challenge.description',
                'challenge.start_date',
                'challenge.end_date',
                'challenge.time_limit',
                'challenge.details',
                'challenge.status',
                'challenge.author_id',
                'challenge.author_name',
                'challenge.score',
                'challenge.is_active',
                'challenge.created_at',
                'COUNT(challenge_participants.id) AS challengers'
            ])
            ->join(
                'challenge_participants',
                'challenge_participants.challenge_id = challenge.id',
                'LEFT'
            )
            ->where('challenge.exam_type_id', $filters['exam_type_id'])
            ->groupBy([
                'challenge.id',
                'challenge.title',
                'challenge.description',
                'challenge.start_date',
                'challenge.end_date',
                'challenge.time_limit',
                'challenge.details',
                'challenge.status',
                'challenge.author_id',
                'challenge.author_name',
                'challenge.score',
                'challenge.is_active',
                'challenge.created_at'
            ])
            ->orderBy('challenge.created_at', 'DESC')
            ->get();

        $now = new DateTime();
        $grouped = [
            'recommended' => [],
            'personal' => [],
            'active' => [],
            'upcoming' => [],
            'completed' => []
        ];

        foreach ($challenges as &$challenge) {
            $start = new DateTime($challenge['start_date']);
            $end = new DateTime($challenge['end_date']);
            //die($start <= $now && $end >= $now);
            $challenge['details'] = json_decode($challenge['details'], true);
            $challenge['status'] = ChallengeStatus::fromValue(
                (int)$challenge['status']
            )->label();
            $challenge['is_active'] = $challenge['is_active'] === 1 ? 'active' : 'inactive';

            // Auto-update expired challenges
            if ($challenge['is_active'] === 'active' && $end < $now) {
                $challenge['is_active'] = 'inactive';

                $this->challenge
                    ->where('id', $challenge['id'])
                    ->update(['is_active' => 0]);
            }

            // Determine visibility
            $isOwner = $challenge['author_id'] == $filters['author_id'];
            $isActive = $challenge['is_active'] === 'active';
            $isPublished = $challenge['status'] === 'published';
            $hasEnded = $end < $now;
            $userParticipated = !$isOwner && $hasEnded && $this->checkIfUserParticipated($challenge['id'], $filters['author_id']);

            // Skip if not owner AND (not active OR not published) AND user didn't participate
            if (!$isOwner && (!$isActive || !$isPublished) && !$userParticipated) {
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
            } elseif ($userParticipated) {
                // Challenge has ended, user is not owner, but they participated
                $grouped['completed'][] = $challenge;
            }
        }

        return $grouped;
    }

    private function checkIfUserParticipated(int $challengeId, int $userId): bool
    {
        return $this->challengeParticipants
            ->where('challenge_id', $challengeId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Summary of getChallengeQuestions
     * @param array $filters
     * @return array
     */
    public function getChallengeQuestions(array $filters): array
    {
        $challenge = $this->challenge
            ->select(['id', 'question_ids'])
            ->where('id', $filters['challenge_id'])
            ->first();

        if (!$challenge) {
            return [];
        }

        $questionIdsData = $this->decode($challenge['question_ids']);
        $questionIds = $questionIdsData[$filters['exam_id']] ?? [];

        if (empty($questionIds)) {
            return [];
        }

        return $this->questionService->fetchQuestions($questionIds, ['shuffle' => true]);
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
