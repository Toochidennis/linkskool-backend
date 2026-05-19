<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\Str;
use V3\App\Common\Utilities\Uuid;
use V3\App\Models\Explore\Classroom\ClassroomCourseLesson;
use V3\App\Services\Explore\StorageService;

class ClassroomCourseLessonService
{
    protected ClassroomCourseLesson $model;
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo   = $pdo;
        $this->model = new ClassroomCourseLesson($pdo);
    }

    public function addLesson(array $data): int|false
    {
        try {
            $this->pdo->beginTransaction();

            $payload = [
                'slug'                       => Uuid::v4(),
                'course_id'                  => $data['course_id'],
                'institution_id'             => $data['institution_id'],
                'title'                      => $data['title'],
                'description'                => $data['description'] ?? null,
                'goals'                      => $data['goals'] ?? null,
                'objectives'                 => $data['objectives'] ?? null,
                'video_url'                  => $data['video_url'] ?? null,
                'recorded_video_url'         => $data['recorded_video_url'] ?? null,
                'zoom_info'                  => json_encode($data['zoom_info'] ?? []),
                'display_order'              => $data['display_order'],
                'write_up_content'           => $data['write_up_content'] ?? null,
                'assignment_instructions'    => $data['assignment_instructions'] ?? null,
                'assignment_due_date'        => !empty($data['assignment_due_date']) ? $data['assignment_due_date'] : null,
                'assignment_submission_type' => $data['assignment_submission_type'] ?? 'upload',
                'is_final_lesson'            => $data['is_final_lesson'] ?? false,
                'author_name'                => $data['author_name'],
                'author_id'                  => $data['author_id'],
                'lesson_date'                => $data['lesson_date'],
                'status'                     => $data['status'] ?? 'draft',
            ];

            if ($data['is_final_lesson'] && !isset($_FILES['certificate'])) {
                throw new \Exception('Certificate file is required for final lessons.');
            }

            if (!empty($data['video_url']) && $this->isYouTubeUrl($data['video_url'])) {
                $payload['thumbnail'] = $this->getYouTubeThumbnail($data['video_url']);
            }

            $fileUrls = $this->processNewFiles($data);
            $payload  = [...$payload, ...$fileUrls];

            $lessonId = $this->model->insert($payload);
            if (!$lessonId) {
                throw new \Exception('Failed to insert lesson record.');
            }

            $this->pdo->commit();

            return $lessonId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateLesson(array $data): bool
    {
        try {
            $this->pdo->beginTransaction();

            $payload = [
                'title'                      => $data['title'],
                'description'                => $data['description'] ?? null,
                'goals'                      => $data['goals'] ?? null,
                'objectives'                 => $data['objectives'] ?? null,
                'video_url'                  => $data['video_url'] ?? null,
                'recorded_video_url'         => $data['recorded_video_url'] ?? null,
                'zoom_info'                  => json_encode($data['zoom_info'] ?? []),
                'display_order'              => $data['display_order'],
                'write_up_content'           => $data['write_up_content'] ?? null,
                'assignment_instructions'    => $data['assignment_instructions'] ?? null,
                'assignment_due_date'        => !empty($data['assignment_due_date']) ? $data['assignment_due_date'] : null,
                'assignment_submission_type' => $data['assignment_submission_type'] ?? 'upload',
                'is_final_lesson'            => $data['is_final_lesson'] ?? false,
                'author_name'                => $data['author_name'],
                'author_id'                  => $data['author_id'],
                'lesson_date'                => $data['lesson_date'],
                'status'                     => $data['status'] ?? 'draft',
                'updated_at'                 => date('Y-m-d H:i:s'),
            ];

            if ($data['is_final_lesson'] && !isset($_FILES['certificate'])) {
                throw new \Exception('Certificate file is required for final lessons.');
            }

            if (!empty($data['video_url']) && $this->isYouTubeUrl($data['video_url'])) {
                $payload['thumbnail'] = $this->getYouTubeThumbnail($data['video_url']);
            }

            $fileUrls = $this->processNewFiles($data, isUpdate: true);
            $payload  = [...$payload, ...$fileUrls];

            $updated = $this->model
                ->where('id', $data['lesson_id'])
                ->update(array_filter($payload, fn($v) => $v !== null));

            if (!$updated) {
                throw new \Exception('Failed to update lesson record.');
            }

            $this->cleanupOldFiles($data, $fileUrls);

            $this->pdo->commit();

            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $lessonId, string $status): bool
    {
        return $this->model
            ->where('id', $lessonId)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function getLessonsByCourseId(int $courseId): array
    {
        $sql = "
            SELECT *
            FROM classroom_course_lessons
            WHERE course_id = :course_id
            ORDER BY display_order ASC
        ";

        $rows = $this->model->rawQuery($sql, ['course_id' => $courseId]);

        return array_map(function (array $row): array {
            $row['zoom_info'] = !empty($row['zoom_info']) ? json_decode($row['zoom_info'], true) : null;
            return $row;
        }, $rows);
    }

    public function deleteLesson(int $id): bool
    {
        $lesson = $this->model->where('id', $id)->first();

        if (!$lesson) {
            return true;
        }

        if (!empty($lesson['material_url'])) {
            StorageService::deleteFile($lesson['material_url']);
        }
        if (!empty($lesson['assignment_url'])) {
            StorageService::deleteFile($lesson['assignment_url']);
        }
        if (!empty($lesson['certificate_url'])) {
            StorageService::deleteFile($lesson['certificate_url']);
        }

        return $this->model->where('id', $id)->delete();
    }

    private function processNewFiles(array $data, bool $isUpdate = false): array
    {
        $urls = [
            'material_url'    => null,
            'assignment_url'  => null,
            'certificate_url' => null,
        ];

        $material = $_FILES['material'] ?? null;

        if (!$material && !$isUpdate) {
            throw new \Exception('Material file is required.');
        }

        if ($material) {
            $urls['material_url'] = StorageService::saveFile(
                $material,
                $this->buildLessonPath($data, 'materials')
            );
        }

        if (isset($_FILES['assignment'])) {
            $urls['assignment_url'] = StorageService::saveFile(
                $_FILES['assignment'],
                $this->buildLessonPath($data, 'assignments')
            );
        }

        if (isset($_FILES['certificate'])) {
            $urls['certificate_url'] = StorageService::saveFile(
                $_FILES['certificate'],
                $this->buildLessonPath($data, 'certificates')
            );
        }

        return $urls;
    }

    private function cleanupOldFiles(array $data, array $fileUrls): void
    {
        if (isset($data['old_material_url'], $fileUrls['material_url'])) {
            StorageService::deleteFile($data['old_material_url']);
        }
        if (isset($data['old_assignment_url'], $fileUrls['assignment_url'])) {
            StorageService::deleteFile($data['old_assignment_url']);
        }
        if (isset($data['old_certificate_url'], $fileUrls['certificate_url'])) {
            StorageService::deleteFile($data['old_certificate_url']);
        }
    }

    private function buildLessonPath(array $data, string $assetType): string
    {
        $institutionId = (int) ($data['institution_id'] ?? 0);
        $courseId      = (int) ($data['course_id'] ?? 0);
        $lessonSlug    = Str::slug((string) ($data['title'] ?? 'lesson'));

        return "explore/classrooms/institutions/{$institutionId}/courses/{$courseId}/lessons/{$lessonSlug}/{$assetType}";
    }

    private function isYouTubeUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return \in_array($host, [
            'www.youtube.com',
            'youtube.com',
            'm.youtube.com',
            'youtu.be',
        ], true);
    }

    private function getYouTubeVideoId(string $url): ?string
    {
        if (preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
            return $matches[1];
        }
        if (preg_match('/youtu\.be\/([^?&]+)/', $url, $matches)) {
            return $matches[1];
        }
        if (preg_match('/\/embed\/([^?&]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function getYouTubeThumbnail(string $url): ?string
    {
        $videoId = $this->getYouTubeVideoId($url);

        return $videoId
            ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg"
            : null;
    }
}
