<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\Str;
use V3\App\Common\Utilities\Uuid;
use V3\App\Models\Explore\Classroom\ClassroomCourseLesson;
use V3\App\Models\Explore\Classroom\ClassroomCourseLessonAssignment;
use V3\App\Models\Explore\Classroom\ClassroomCourseLessonFile;
use V3\App\Services\Explore\StorageService;

class ClassroomCourseLessonService
{
    protected ClassroomCourseLesson $model;
    private ClassroomCourseLessonAssignment $assignmentModel;
    private ClassroomCourseLessonFile $fileModel;
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo             = $pdo;
        $this->model           = new ClassroomCourseLesson($pdo);
        $this->assignmentModel = new ClassroomCourseLessonAssignment($pdo);
        $this->fileModel       = new ClassroomCourseLessonFile($pdo);
    }

    public function addLesson(array $data): int|false
    {
        try {
            $this->pdo->beginTransaction();

            $payload = [
                'slug'               => Uuid::v4(),
                'course_id'          => $data['course_id'],
                'institution_id'     => $data['institution_id'],
                'title'              => $data['title'],
                'description'        => $data['description'] ?? null,
                'goals'              => $data['goals'] ?? null,
                'objectives'         => $data['objectives'] ?? null,
                'video_url'          => $data['video_url'] ?? null,
                'recorded_video_url' => $data['recorded_video_url'] ?? null,
                'zoom_info'          => json_encode($data['zoom_info'] ?? []),
                'display_order'      => $data['display_order'],
                'write_up_content'   => $data['write_up_content'] ?? null,
                'is_final_lesson'    => $data['is_final_lesson'] ?? false,
                'author_name'        => $data['author_name'],
                'author_id'          => $data['author_id'],
                'lesson_date'        => $data['lesson_date'],
                'status'             => $data['status'] ?? 'draft',
            ];

            if ($data['is_final_lesson'] && !isset($_FILES['certificate'])) {
                throw new \RuntimeException('Certificate file is required for final lessons.');
            }

            if (!empty($data['video_url']) && $this->isYouTubeUrl($data['video_url'])) {
                $payload['thumbnail'] = $this->getYouTubeThumbnail($data['video_url']);
            }

            $lessonId = $this->model->insert($payload);
            if (!$lessonId) {
                throw new \RuntimeException('Failed to insert lesson record.');
            }

            $this->saveAssignment($lessonId, $data);
            $this->saveFiles($lessonId, $data);

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
                'title'              => $data['title'],
                'description'        => $data['description'] ?? null,
                'goals'              => $data['goals'] ?? null,
                'objectives'         => $data['objectives'] ?? null,
                'video_url'          => $data['video_url'] ?? null,
                'recorded_video_url' => $data['recorded_video_url'] ?? null,
                'zoom_info'          => json_encode($data['zoom_info'] ?? []),
                'display_order'      => $data['display_order'],
                'write_up_content'   => $data['write_up_content'] ?? null,
                'is_final_lesson'    => $data['is_final_lesson'] ?? false,
                'author_name'        => $data['author_name'],
                'author_id'          => $data['author_id'],
                'lesson_date'        => $data['lesson_date'],
                'status'             => $data['status'] ?? 'draft',
                'updated_at'         => date('Y-m-d H:i:s'),
            ];

            if ($data['is_final_lesson'] && !isset($_FILES['certificate'])) {
                throw new \RuntimeException('Certificate file is required for final lessons.');
            }

            if (!empty($data['video_url']) && $this->isYouTubeUrl($data['video_url'])) {
                $payload['thumbnail'] = $this->getYouTubeThumbnail($data['video_url']);
            }

            $updated = $this->model
                ->where('id', $data['lesson_id'])
                ->update(array_filter($payload, fn($v) => $v !== null));

            if (!$updated) {
                throw new \RuntimeException('Failed to update lesson record.');
            }

            $this->updateAssignment((int) $data['lesson_id'], $data);
            $this->replaceFiles((int) $data['lesson_id'], $data);

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
            SELECT l.*,
                   a.instructions    AS assignment_instructions,
                   a.due_date        AS assignment_due_date,
                   a.submission_type AS assignment_submission_type
            FROM classroom_course_lessons l
            LEFT JOIN classroom_course_lesson_assignments a ON a.lesson_id = l.id
            WHERE l.course_id = :course_id
            ORDER BY l.display_order ASC
        ";

        $rows = $this->model->rawQuery($sql, ['course_id' => $courseId]);

        if (empty($rows)) {
            return [];
        }

        $lessonIds = array_column($rows, 'id');
        $files     = $this->getFilesForLessons($lessonIds);

        return array_map(function (array $row) use ($files): array {
            $row['zoom_info'] = !empty($row['zoom_info']) ? json_decode($row['zoom_info'], true) : null;
            $row['files']     = $files[$row['id']] ?? [];
            return $row;
        }, $rows);
    }

    public function deleteLesson(int $id): bool
    {
        $files = $this->fileModel->where('lesson_id', $id)->get();

        foreach ($files as $file) {
            StorageService::deleteFile($file['url']);
        }

        $this->fileModel->where('lesson_id', $id)->delete();
        $this->assignmentModel->where('lesson_id', $id)->delete();

        return $this->model->where('id', $id)->delete();
    }

    private function saveAssignment(int $lessonId, array $data): void
    {
        $hasData = !empty($data['assignment_instructions']) || !empty($data['assignment_due_date']);

        if (!$hasData) {
            return;
        }

        $this->assignmentModel->insert([
            'lesson_id'       => $lessonId,
            'instructions'    => $data['assignment_instructions'] ?? null,
            'due_date'        => $data['assignment_due_date'] ?? null,
            'submission_type' => $data['assignment_submission_type'] ?? 'file',
        ]);
    }

    private function updateAssignment(int $lessonId, array $data): void
    {
        $hasData  = !empty($data['assignment_instructions']) || !empty($data['assignment_due_date']);
        $existing = $this->assignmentModel->where('lesson_id', $lessonId)->first();

        if (!$hasData) {
            if ($existing) {
                $this->assignmentModel->where('lesson_id', $lessonId)->delete();
            }
            return;
        }

        $payload = [
            'instructions'    => $data['assignment_instructions'] ?? null,
            'due_date'        => $data['assignment_due_date'] ?? null,
            'submission_type' => $data['assignment_submission_type'] ?? 'file',
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->assignmentModel->where('lesson_id', $lessonId)->update($payload);
        } else {
            $this->assignmentModel->insert(['lesson_id' => $lessonId, ...$payload]);
        }
    }

    private function saveFiles(int $lessonId, array $data): void
    {
        foreach (['material', 'assignment', 'certificate'] as $type) {
            if (!isset($_FILES[$type])) {
                continue;
            }

            $url = StorageService::saveFile(
                $_FILES[$type],
                $this->buildLessonPath($data, "{$type}s")
            );

            $this->fileModel->insert([
                'lesson_id' => $lessonId,
                'type'      => $type,
                'url'       => $url,
            ]);
        }
    }

    private function replaceFiles(int $lessonId, array $data): void
    {
        foreach (['material', 'assignment', 'certificate'] as $type) {
            if (!isset($_FILES[$type])) {
                continue;
            }

            $existing = $this->fileModel
                ->where('lesson_id', $lessonId)
                ->where('type', $type)
                ->first();

            if ($existing) {
                StorageService::deleteFile($existing['url']);
                $this->fileModel->where('id', $existing['id'])->delete();
            }

            $url = StorageService::saveFile(
                $_FILES[$type],
                $this->buildLessonPath($data, "{$type}s")
            );

            $this->fileModel->insert([
                'lesson_id' => $lessonId,
                'type'      => $type,
                'url'       => $url,
            ]);
        }
    }

    private function getFilesForLessons(array $lessonIds): array
    {
        if (empty($lessonIds)) {
            return [];
        }

        $params       = [];
        $placeholders = [];
        foreach ($lessonIds as $i => $id) {
            $key              = "id{$i}";
            $params[$key]     = $id;
            $placeholders[]   = ":{$key}";
        }

        $sql  = 'SELECT * FROM classroom_course_lesson_files WHERE lesson_id IN (' . implode(',', $placeholders) . ')';
        $rows = $this->fileModel->rawQuery($sql, $params);

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['lesson_id']][] = $row;
        }

        return $grouped;
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
