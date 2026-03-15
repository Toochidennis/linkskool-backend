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
        if (isset($_FILES['image'])) {
            $cohortSlug = $this->toSlug($data['title']);
            $courseSlug = $this->toSlug($data['course_name'] ?? (string)$data['course_id']);

            $data['image_url'] = StorageService::saveFile(
                $_FILES['image'],
                "explore/programs/{$data['program_id']}/courses/{$courseSlug}/cohorts/{$cohortSlug}"
            );
        }

        $slug = $this->generateUuidV4();

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
            'image_url' => $data['image_url'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'delivery_mode' => $data['delivery_mode'] ?? 'virtual',
            'zoom_link' => $data['zoom_link'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'is_free' => $data['is_free'],
            'trial_type' => $data['trial_type'] ?? null,
            'trial_value' => $data['trial_value'] ?? null,
            'cost' => $data['cost'] ?? null,
            'discount' => $data['discount'] ?? null,
            'instructor_name' => $data['instructor_name'] ?? null,
            'learning_type' => $data['learning_type'] ?? 'instructor_led',
            'enrollment_deadline' => $data['enrollment_deadline'] ?? null,
            'whatsapp_group_link' => $data['whatsapp_group_link'] ?? null,
        ];

        if (isset($data['next_cohort']) && !empty($data['next_cohort'])) {
            $payload['next_cohort'] = json_encode($data['next_cohort']);
        }

        $this->cohort
            ->where('program_id', $data['program_id'])
            ->where('course_id', $data['course_id'])
            ->update(['status' => 'completed']);

        return $this->cohort->insert($payload);
    }

    public function updateProgramCourseCohort(array $data)
    {
        if (isset($_FILES['image'])) {
            $cohortSlug = $this->toSlug($data['title']);
            $data['image_url'] = StorageService::saveFile(
                $_FILES['image'],
                "explore/cohorts/{$data['id']}/{$cohortSlug}"
            );
        }

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'benefits' => $data['benefits'],
            'code' => $data['code'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'capacity' => $data['capacity'] ?? null,
            'delivery_mode' => $data['delivery_mode'] ?? 'virtual',
            'zoom_link' => $data['zoom_link'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'is_free' => $data['is_free'],
            'trial_type' => $data['trial_type'] ?? null,
            'trial_value' => $data['trial_value'] ?? null,
            'instructor_name' => $data['instructor_name'] ?? null,
            'learning_type' => $data['learning_type'] ?? 'instructor_led',
            'discount' => $data['discount'] ?? null,
            'cost' => $data['cost'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
            'enrollment_deadline' => $data['enrollment_deadline'] ?? null,
            'whatsapp_group_link' => $data['whatsapp_group_link'] ?? null,
        ];

        if (isset($data['next_cohort']) && !empty($data['next_cohort'])) {
            $payload['next_cohort'] = json_encode($data['next_cohort']);
        }

        if ($data['image_url'] ?? null) {
            $payload['image_url'] = $data['image_url'];
        }

        $id = $this->cohort
            ->where('id', $data['id'])
            ->update(
                $payload
            );

        if (isset($data['old_image_url'])) {
            StorageService::deleteFile($data['old_image_url']);
        }

        return $id;
    }

    public function updateStatus(int $id, string $status)
    {
        return $this->cohort
            ->where('id', $id)
            ->update(['status' => $status]);
    }

    public function getAllCohortsByCourseId(int $programId, int $courseId)
    {
        $rows = $this->cohort
            ->where('program_id', $programId)
            ->where('course_id', $courseId)
            ->orderBy('start_date', 'ASC')
            ->get();

        return array_map(function (array $cohort): array {
            $cohort['next_cohort'] = !empty($cohort['next_cohort'])
                ? json_decode($cohort['next_cohort'], true)
                : null;

            return $cohort;
        }, $rows);
    }

    public function getProgramCohorts(int $programId, ?int $cohortId = null)
    {
        $query =  $this->cohort
            ->select(['id, title, description, start_date, end_date'])
            ->where('program_id', $programId);

        if ($cohortId !== null) {
            $query->notIn('id', [$cohortId]);
        }

        return $query->orderBy('start_date', 'ASC')
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

    private function toSlug(string $text): string
    {
        return strtolower(trim((string)preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }

    private function generateUuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
