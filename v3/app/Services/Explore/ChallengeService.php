<?php

namespace V3\App\Services\Explore;

use DateTime;
use V3\App\Models\Explore\Challenge;
use V3\App\Common\Enums\ChallengeStatus;
use V3\App\Models\Explore\ChallengeItem;
use V3\App\Models\Explore\ChallengeParticipants;
use V3\App\Models\Portal\ELearning\Quiz;

class ChallengeService
{
    private Challenge $challenge;
    private ChallengeItem $challengeItem;
    private Quiz $quiz;
    private QuestionService $questionService;
    private ChallengeParticipants $challengeParticipants;
    private \PDO $pdo;

    private const ADMIN_USER = 'admin';

    public function __construct(\PDO $pdo)
    {
        $this->challenge = new Challenge($pdo);
        $this->challengeItem = new ChallengeItem($pdo);
        $this->quiz = new Quiz($pdo);
        $this->questionService = new QuestionService($pdo);
        $this->challengeParticipants = new ChallengeParticipants($pdo);
        $this->pdo = $pdo;
    }

    /**
     * Summary of createChallenge
     * @param array $data
     * @return bool|int
     */
    public function createChallenge(array $data)
    {
        $this->pdo->beginTransaction();
        try {
            $payload = [
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'duration' => $data['duration'],
                'exam_type_id' => $data['exam_type_id'],
                'status' => ChallengeStatus::fromLabel($data['status'])->value,
                'author_id' => $data['author_id'],
                'author_name' => $data['author_name'],
                'published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null,
            ];

            $challengeId = $this->challenge->insert($payload);

            if (!$challengeId) {
                $this->pdo->rollBack();
                return false;
            }

            foreach ($data['items'] as $index => $item) {
                $questionIds = $this->getQuestionIds(
                    $data['exam_type_id'],
                    $item['course_id'],
                    $item['question_count'],
                    $item['years']
                );
                if (empty($questionIds)) {
                    $this->pdo->rollBack();
                    return false;
                }
                $challengeItemPayload = [
                    'challenge_id' => $challengeId,
                    'course_id' => $item['course_id'],
                    'course_name' => $item['course_name'],
                    'question_count' => $item['question_count'],
                    'years' => json_encode($item['years']),
                    'question_ids' => json_encode($questionIds),
                    'sort_order' => $index + 1,
                ];

                $inserted = $this->challengeItem->insert($challengeItemPayload);

                if (!$inserted) {
                    $this->pdo->rollBack();
                    return false;
                }
            }

            $this->pdo->commit();
            return $challengeId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function updateChallenge(array $data): bool
    {
        $this->pdo->beginTransaction();
        try {
            $payload = [
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'duration' => $data['duration'],
                'exam_type_id' => $data['exam_type_id'],
                'status' => ChallengeStatus::fromLabel($data['status'])->value,
                'author_id' => $data['author_id'],
                'author_name' => $data['author_name'],
                'updated_at' => date('Y-m-d H:i:s'),
                'is_active' => 1,
                'published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null,
            ];

            $updated = $this->challenge
                ->where('id', $data['challenge_id'])
                ->update($payload);

            if (!$updated) {
                $this->pdo->rollBack();
                return false;
            }

            // Delete existing items
            $this->challengeItem
                ->where('challenge_id', $data['challenge_id'])
                ->delete();

            // Insert new items
            foreach ($data['items'] as $index => $item) {
                $questionIds = $this->getQuestionIds(
                    $data['exam_type_id'],
                    $item['course_id'],
                    $item['question_count'],
                    $item['years']
                );
                if (empty($questionIds)) {
                    $this->pdo->rollBack();
                    return false;
                }
                $challengeItemPayload = [
                    'challenge_id' => $data['challenge_id'],
                    'course_id' => $item['course_id'],
                    'course_name' => $item['course_name'],
                    'question_count' => $item['question_count'],
                    'years' => json_encode($item['years']),
                    'question_ids' => json_encode($questionIds),
                    'sort_order' => $index + 1,
                ];

                $inserted = $this->challengeItem->insert($challengeItemPayload);

                if (!$inserted) {
                    $this->pdo->rollBack();
                    return false;
                }
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
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
        $this->pdo->beginTransaction();

        try {
            $deleted = $this->challenge
                ->where('id', '=', $challengeId)
                ->where('author_id', '=', $authorId)
                ->delete();

            if (!$deleted) {
                $this->pdo->rollBack();
                return false;
            }

            $this->challengeItem
                ->where('challenge_id', '=', $challengeId)
                ->delete();

            $this->pdo->commit();

            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    private function getQuestionIds(int $examTypeId, int $courseId, int $questionCount, array $years): array
    {
        $questionIds = $this->quiz
            ->select(['question_id'])
            ->where('exam_type', $examTypeId)
            ->where('course_id', $courseId)
            ->in('year', $years)
            ->get();

        if (empty($questionIds)) {
            return [];
        }

        $questionIds = array_column($questionIds, 'question_id');
        shuffle($questionIds);

        return \array_slice($questionIds, 0, $questionCount);
    }

    /**
     * Summary of getChallenges
     * @param int $authorId
     * @return array[]|array{active: array, personal: array, recommended: array, upcoming: array, completed: array}
     */
    public function getChallenges(array $filters): array
    {
        $sql = "
            SELECT
                c.id,
                c.title,
                c.description,
                c.start_date,
                c.end_date,
                c.duration,
                c.exam_type_id,
                c.status,
                c.author_id,
                c.author_name,
                c.is_active,
                c.created_at,
                COUNT(cp.id) AS challengers
            FROM challenge c
            LEFT JOIN challenge_participants cp
                ON cp.challenge_id = c.id
            WHERE c.exam_type_id = :exam_type_id
            GROUP BY
                c.id,
                c.title,
                c.description,
                c.start_date,
                c.end_date,
                c.duration,
                c.exam_type_id,
                c.status,
                c.author_id,
                c.author_name,
                c.is_active,
                c.created_at
            ORDER BY c.created_at DESC
        ";

        $challenges = $this->challenge->rawQuery($sql, [
            'exam_type_id' => $filters['exam_type_id'],
        ]);

        $challengeSubjects = $this->getChallengeSubjects(
            array_column($challenges, 'id')
        );

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
            $challenge['exam_type_id'] = (int) $challenge['exam_type_id'];
            $challenge['subjects'] = $challengeSubjects[(int) $challenge['id']] ?? [];
            $challenge['status'] = ChallengeStatus::fromValue(
                (int)$challenge['status']
            )->label();
            $challenge['is_active'] = $challenge['is_active'] === 1 ? 'active' : 'inactive';
            $challenge['challengers'] = (int) $challenge['challengers'];

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

    private function getChallengeSubjects(array $challengeIds): array
    {
        if (empty($challengeIds)) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach (array_values($challengeIds) as $index => $challengeId) {
            $key = 'challenge_id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $challengeId;
        }

        $sql = "
            SELECT
                id,
                challenge_id,
                course_id,
                course_name,
                question_count,
                years
            FROM challenge_items
            WHERE challenge_id IN (" . implode(', ', $placeholders) . ")
            ORDER BY challenge_id ASC, sort_order ASC, id ASC
        ";

        $rows = $this->challengeItem->rawQuery($sql, $params);

        $subjectsByChallenge = [];

        foreach ($rows as $row) {
            $challengeId = (int) $row['challenge_id'];

            $subjectsByChallenge[$challengeId][] = [
                'course_id' => (int) $row['course_id'],
                'course_name' => $row['course_name'],
                'years' => $this->decode($row['years']),
                'question_count' => (int) $row['question_count'],
            ];
        }

        return $subjectsByChallenge;
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
        $challengeItem = $this->challengeItem
            ->select(['question_ids'])
            ->where('challenge_id', $filters['challenge_id'])
            ->where('course_id', $filters['course_id'])
            ->first();

        if (!$challengeItem) {
            return [];
        }

        $questionIds = $this->decode($challengeItem['question_ids']);

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
