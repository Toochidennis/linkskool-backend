<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\ProgramCohortCourseEnrollment;

class CourseCohortEnrollmentService
{
    protected ProgramCohortCourseEnrollment $enrollmentModel;

    public function __construct(\PDO $pdo)
    {
        $this->enrollmentModel = new ProgramCohortCourseEnrollment($pdo);
    }

    public function enrollUser(array $data): bool|int
    {
        $payload = [
            'profile_id' => $data['profile_id'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'cohort_name' => $data['cohort_name'],
            'cohort_id' => $data['cohort_id'],
            'program_id' => $data['program_id'],
            'enrollment_type' => $data['enrollment_type'],
        ];

        return $this->enrollmentModel->insert($payload);
    }

    public function unEnrollUser(array $data): bool
    {
        return $this->enrollmentModel
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->delete();
    }

    public function isUserEnrolled(array $data): bool
    {
        $enrollment = $this->enrollmentModel
            ->where('profile_id', $data['profile_id'])
            ->where('cohort_id', $data['cohort_id'])
            ->first();

        return $enrollment !== false;
    }

    public function updateStatus(array $filters): bool
    {
        $payload = [
            'status' => $filters['status'],
        ];

        return $this->enrollmentModel
            ->where('profile_id', $filters['profile_id'])
            ->where('cohort_id', $filters['cohort_id'])
            ->update($payload);
    }
}
