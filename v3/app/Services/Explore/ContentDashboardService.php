<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\AuditLog;
use V3\App\Models\Explore\Exam;
use V3\App\Models\Explore\ExamType;
use V3\App\Models\Explore\User;
use V3\App\Models\Portal\Academics\Course;

class ContentDashboardService
{
    private User $user;
    private ExamType $examType;
    private AuditLog $auditLog;
    private Exam $exam;
    private Course $course;

    public function __construct(\PDO $pdo)
    {
        $this->user = new User($pdo);
        $this->examType = new ExamType($pdo);
        $this->auditLog = new AuditLog($pdo);
        $this->exam = new Exam($pdo);
        $this->course = new Course($pdo);
    }

    private function getTotalUsers(): int
    {
        return $this->user->where('accessLevel', '<>', 1)->count();
    }

    private function getTotalExamTypes(): int
    {
        return $this->examType->where('is_active', 1)->count();
    }

    private function getTotalExams(): int
    {
        return $this->exam->count();
    }

    private function getTotalCourses(): int
    {
        return $this->course->count();
    }

    public function getDashboardData(): array
    {
        return [
            'total_users' => $this->getTotalUsers(),
            'total_programs' => $this->getTotalExamTypes(),
            'total_exams' => $this->getTotalExams(),
            'total_courses' => $this->getTotalCourses()
        ];
    }

    public function getActivityLogs(array $filters): array
    {
        return $this->auditLog
            ->orderBy('created_at', 'DESC')
            ->paginate($filters['page'] ?? 1, $filters['limit'] ?? 25);
    }
}
