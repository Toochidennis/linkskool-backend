<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\ExamAttempt;

class ExploreAttemptService
{
    private ExamAttempt $examAttempt;

    public function __construct(\PDO $pdo)
    {
        $this->examAttempt = new ExamAttempt($pdo);
    }

    public function createExamAttempt(array $data): int
    {
        return $this->examAttempt->insert($data);
    }

    public function getExamAttemptsByUser(int $userId): array
    {
        return $this->examAttempt
            ->where('user_id', '=', $userId)
            ->get();
    }
}
