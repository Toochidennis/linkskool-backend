<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class ProgramCourseCohortService
{
    protected ProgramCourseCohort $cohort;
    private ProgramCourseCohortLesson $cohortLesson;
    public function __construct(\PDO $pdo)
    {
        $this->cohort = new ProgramCourseCohort($pdo);
        $this->cohortLesson = new ProgramCourseCohortLesson($pdo);
    }

    public function addCohortToProgramCourse(array $data)
    {
        if (!isset($_FILES['image'])) {
            throw new \Exception("Invalid image upload.");
        }

        $data['image_url'] = StorageService::saveFile($_FILES['image']);

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));

        $payload = [
            'slug' => $slug,
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'program_id' => $data['program_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'benefits' => $data['benefits'],
            'code' => $data['code'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'image_url' => $data['image_url'],
            'capacity' => $data['capacity'] ?? null,
            'delivery_mode' => $data['delivery_mode'] ?? null,
            'zoom_link' => $data['zoom_link'] ?? null,
            'is_free' => $data['is_free'],
            'trial_type' => $data['trial_type'] ?? null,
            'trial_value' => $data['trial_value'] ?? null,
            'cost' => $data['cost'] ?? null,
            'instructor_name' => $data['instructor_name'] ?? null,
        ];

        $this->cohort->rawExecute(
            "UPDATE program_course_cohorts
                SET `status` = 'completed'
                WHERE course_id = :course_id",
            ['course_id' => $data['course_id']]
        );

        return $this->cohort->insert($payload);
    }

    public function updateProgramCourseCohort(array $data)
    {
        if (isset($_FILES['image'])) {
            $data['image_url'] = StorageService::saveFile($_FILES['image']);
        }

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'benefits' => $data['benefits'],
            'code' => $data['code'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'image_url' => $data['image_url'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'delivery_mode' => $data['delivery_mode'] ?? null,
            'zoom_link' => $data['zoom_link'] ?? null,
            'is_free' => $data['is_free'],
            'trial_type' => $data['trial_type'] ?? null,
            'trial_value' => $data['trial_value'] ?? null,
            'instructor_name' => $data['instructor_name'] ?? null,
            'cost' => $data['cost'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $id = $this->cohort
            ->where('id', $data['id'])
            ->update(
                array_filter(
                    $payload,
                    fn($value) => $value !== null
                )
            );

        return $id;
    }

    public function updateStatus(int $id, string $status)
    {
        return $this->cohort
            ->where('id', $id)
            ->update(['status' => $status]);
    }

    public function getAllCohortsByCourseId(int $courseId)
    {
        return $this->cohort
            ->where('course_id', $courseId)
            ->orderBy('start_date', 'ASC')
            ->get();
    }

    public function deleteCohort(int $id)
    {
        $lesson = $this->cohortLesson
            ->where('cohort_id', $id)
            ->first();

        if (!empty($lesson)) {
            throw new \InvalidArgumentException("Cannot delete cohort with existing lessons.");
        }

        $cohort = $this->cohort
            ->where('id', $id)
            ->first();

        if ($cohort && !empty($cohort['image_url'])) {
            StorageService::deleteFile($cohort['image_url']);
        }

        return $this->cohort
            ->where('id', $id)
            ->delete();
    }
}
