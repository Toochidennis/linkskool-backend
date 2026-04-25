<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CbtGamify;

class CbtGamifyService
{
    private CbtGamify $cbtGamifyModel;

    public function __construct(\PDO $pdo)
    {
        $this->cbtGamifyModel = new CbtGamify($pdo);
    }

    public function storeLeaderboardData(array $data): bool
    {
        if ($this->checkIfRecordExists($data['user_id'], $data['exam_type_id'], $data['course_id'])) {
            return $this->cbtGamifyModel
                ->where('user_id', $data['user_id'])
                ->where('exam_type_id', $data['exam_type_id'])
                ->where('course_id', $data['course_id'])
                ->update($data);
        }
        return $this->cbtGamifyModel->insert($data);
    }

    public function checkIfRecordExists(int $userId, int $examTypeId, int $courseId): bool
    {
        return $this->cbtGamifyModel
            ->where('user_id', $userId)
            ->where('exam_type_id', $examTypeId)
            ->where('course_id', $courseId)
            ->exists();
    }

    public function getLeaderboardData(): array
    {
        return $this->cbtGamifyModel
            ->select(['user_id', 'username', 'exam_type_id', 'course_id', 'course_name', 'score'])
            ->orderBy('score', 'DESC')
            ->get();
    }

    public function getLeaderboardSummary(int $userId, int $examTypeId): array
    {
        $results = $this->cbtGamifyModel
            ->select(['course_id', 'course_name', 'exam_type_id', 'user_id', 'username', 'score'])
            ->where('exam_type_id', $examTypeId)
            ->orderBy('course_id', 'ASC')
            ->orderBy('score', 'DESC')
            ->get();

        $subjectMap = [];
        $userTotals = [];

        foreach ($results as $record) {
            $courseId = $record['course_id'];
            $recordUserId = (int) $record['user_id'];
            $score  = (float) $record['score'];

            // --- Per-subject aggregation ---
            if (!isset($subjectMap[$courseId])) {
                $subjectMap[$courseId] = [
                    'course_id' => $courseId,
                    'course_name' => $record['course_name'],
                    'exam_type_id' => $examTypeId,
                    'total_participants' => 0,
                    'top_participants'   => [],
                    'user_score' => null,
                    'user_rank' => null,
                ];
            }

            $subjectMap[$courseId]['total_participants']++;

            // Track top 3 per subject (already sorted DESC by score)
            if (count($subjectMap[$courseId]['top_participants']) < 3) {
                $subjectMap[$courseId]['top_participants'][] = [
                    'rank'  => count($subjectMap[$courseId]['top_participants']) + 1,
                    'score' => $score,
                ];
            }

            // Track user's score and rank in this subject
            if ($recordUserId === $userId && $subjectMap[$courseId]['user_score'] === null) {
                $subjectMap[$courseId]['user_score'] = $score;
                $subjectMap[$courseId]['user_rank']  = $subjectMap[$courseId]['total_participants'];
            }

            // --- Overall totals per user ---
            if (!isset($userTotals[$recordUserId])) {
                $userTotals[$recordUserId] = [
                    'user_id' => $recordUserId,
                    'username' => $record['username'],
                    'total_score' => 0,
                ];
            }
            $userTotals[$recordUserId]['total_score'] += $score;
        }

        // Sort overall totals by total_score DESC
        usort($userTotals, fn($a, $b) => $b['total_score'] <=> $a['total_score']);

        // Compute user's overall rank and score
        $userOverallRank  = null;
        $userOverallScore = null;
        foreach ($userTotals as $rank => $entry) {
            if ($entry['user_id'] === $userId) {
                $userOverallRank  = $rank + 1;
                $userOverallScore = $entry['total_score'];
                break;
            }
        }

        // Top 3 overall
        $topThreeOverall = [];
        foreach (\array_slice($userTotals, 0, 3) as $rank => $entry) {
            $topThreeOverall[] = [
                'rank' => $rank + 1,
                'user_id' => $entry['user_id'],
                'username' => $entry['username'],
                'total_score' => $entry['total_score'],
            ];
        }

        // Reshape subjects: split top_participants into named positions
        $subjects = array_values(array_map(function (array $subject) {
            $top = $subject['top_participants'];
            return [
                'course_id'          => $subject['course_id'],
                'course_name'        => $subject['course_name'],
                'exam_type_id'       => $subject['exam_type_id'],
                'total_participants' => $subject['total_participants'],
                'champion'           => $top[0] ?? null,
                'runner_up'          => $top[1] ?? null,
                'third_place'        => $top[2] ?? null,
            ];
        }, $subjectMap));

        return [
            'user_stats' => [
                'user_id' => $userId,
                'overall_score' => $userOverallScore,
                'overall_rank'  => $userOverallRank,
            ],
            'subjects'  => $subjects,
            'top_three_overall' => $topThreeOverall,
        ];
    }

    public function getFullLeaderboard(int $examTypeId, ?int $courseId = null, int $page = 1, int $perPage = 50): array
    {
        // Build the query
        $query = $this->cbtGamifyModel
            ->select(['user_id', 'username', 'course_id', 'course_name', 'exam_type_id', 'score'])
            ->where('exam_type_id', $examTypeId);

        if ($courseId !== null) {
            $query->where('course_id', $courseId);
        }

        // Order by score descending
        $query->orderBy('score', 'DESC');

        // Get paginated results
        $leaderboardData = $query->paginate($page, $perPage);

        // Add rank to each record
        $startRank = (($page - 1) * $perPage) + 1;
        foreach ($leaderboardData['data'] as &$record) {
            $record['rank'] = $startRank++;
        }

        return [
            'data' => $leaderboardData['data'],
            'pagination' => $leaderboardData['meta'],
        ];
    }
}
