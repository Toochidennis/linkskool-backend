<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\AuditLog;
use V3\App\Models\Explore\Exam;
use V3\App\Models\Explore\ExamType;
use V3\App\Models\Explore\User;

class ContentDashboardService
{
    private User $user;
    private ExamType $examType;
    private AuditLog $auditLog;
    private Exam $exam;

    public function __construct(\PDO $pdo)
    {
        $this->user = new User($pdo);
        $this->examType = new ExamType($pdo);
        $this->auditLog = new AuditLog($pdo);
        $this->exam = new Exam($pdo);
    }

    private function getTotalUsers(): int
    {
        return $this->user->where('accesslevel', '<>', 1)->count();
    }

    private function getTotalExamTypes(): int
    {
        return $this->examType->where('active', 1)->count();
    }

    private function getTotalExams(): int
    {
        return $this->exam->count();
    }

    private function getTotalActivityLogs(): int
    {
        return $this->auditLog->count();
    }

    public function getActivityLogs(int $limit = 10): array
    {
        return $this->auditLog
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getDashboardData(): array
    {
        return [
            'total_users' => $this->getTotalUsers(),
            'total_programs' => $this->getTotalExamTypes(),
            'total_exams' => $this->getTotalExams(),
            'total_activity_logs' => $this->getTotalActivityLogs(),
            'recent_activity_logs' => $this->getActivityLogs(30),
        ];
    }
}
