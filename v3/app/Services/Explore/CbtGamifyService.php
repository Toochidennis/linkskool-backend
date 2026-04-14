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

        $summary = [];

        foreach ($results as $record) {
            $courseId = $record['course_id'];

            // Initialize course data if not exists
            if (!isset($summary[$courseId])) {
                $summary[$courseId] = [
                    'course_id' => $courseId,
                    'course_name' => $record['course_name'],
                    'exam_type_id' => $examTypeId,
                    'total_participants' => 0,
                    'champion' => [
                        'user_id' => null,
                        'username' => null,
                        'score' => 0,
                    ],
                    'user_score' => null,
                ];
            }

            $summary[$courseId]['total_participants']++;

            // Set champion (first record per course is highest score due to ORDER BY)
            if ($summary[$courseId]['champion']['user_id'] === null) {
                $summary[$courseId]['champion'] = [
                    'user_id' => $record['user_id'],
                    'username' => $record['username'],
                    'score' => $record['score'],
                ];
            }

            // Store user's score for this course
            if ($record['user_id'] == $userId) {
                $summary[$courseId]['user_score'] = $record['score'];
            }
        }

        // Convert to indexed array and return
        return array_values($summary);
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
