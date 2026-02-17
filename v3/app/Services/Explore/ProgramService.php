<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Program;
use V3\App\Models\Explore\ProgramCourse;

class ProgramService
{
    protected Program $programModel;
    private ProgramCourse $programCourseModel;

    public function __construct(\PDO $pdo)
    {
        $this->programModel = new Program($pdo);
        $this->programCourseModel = new ProgramCourse($pdo);
    }

    public function createProgram(array $data): bool
    {
        if (!isset($_FILES['image'])) {
            throw new \Exception("Invalid image upload.");
        }
        $slug = $this->toSlug($data['name']);
        $data['image_url'] = StorageService::saveFile(
            $_FILES['image'],
            "explore/programs/{$slug}"
        );


        $payload = [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'image_url' => $data['image_url'],
            'author_name' => $data['author_name'],
            'author_id' => $data['author_id'],
            'shortname' => $data['shortname'],
            'age_groups' => json_encode($data['age_groups']),
            'status' => $data['status'],
            'sponsor' => $data['sponsor'] ?? null,
        ];

        $id = $this->programModel->insert($payload);

        $this->upsertProgramCourses($data['courses'], $id);

        return $id;
    }

    public function updateProgram(array $data): bool
    {
        if (isset($_FILES['image'])) {
            $slug = $this->toSlug($data['name']);
            $data['image_url'] = StorageService::saveFile(
                $_FILES['image'],
                "explore/programs/{$slug}"
            );
        }

        $payload = [
            'name' => $data['name'],
            'description' => $data['description'],
            'image_url' => $data['image_url'] ?? null,
            'shortname' => $data['shortname'] ?? null,
            'status' => $data['status'] ?? null,
            'sponsor' => $data['sponsor'] ?? null,
        ];

        if (isset($data['age_groups'])) {
            $payload['age_groups'] = json_encode($data['age_groups']);
        }

        $id = $this->programModel
            ->where('id', $data['id'])
            ->update(
                array_filter(
                    $payload,
                    fn($value) => $value !== null
                )
            );

        $this->upsertProgramCourses($data['courses'], $data['id']);

        if (!empty($data['old_image_url']) && isset($data['image_url'])) {
            StorageService::deleteFile($data['old_image_url']);
        }

        return $id;
    }

    private function upsertProgramCourses(array $courses, int $programId): void
    {
        $existing = $this->programCourseModel
            ->where('program_id', $programId)
            ->get();

        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[(int) $row['course_id']] = $row;
        }


        $incomingIds = $incomingIds = array_map(
            static fn($id) => (int) $id,
            array_column($courses, 'id')
        );


        foreach ($courses as $index => $course) {
            if (isset($existingMap[$course['id']])) {
                // Reactivate + update order
                $this->programCourseModel
                    ->where('program_id', $programId)
                    ->where('course_id', $course['id'])
                    ->update([
                        'is_active' => 1,
                        'display_order' => $index + 1,
                    ]);
            } else {
                // New relation
                $this->programCourseModel->insert([
                    'program_id' => $programId,
                    'course_id' => $course['id'],
                    'display_order' => $index + 1,
                    'is_active' => 1,
                ]);
            }
        }

        foreach ($existingMap as $courseId => $row) {
            if (!\in_array($courseId, $incomingIds, true)) {
                $this->programCourseModel
                    ->where('program_id', $programId)
                    ->where('course_id', $courseId)
                    ->update([
                        'is_active'       => 0,
                        'deactivated_at'  => date('Y-m-d H:i:s'),
                    ]);
            }
        }
    }

    public function updateStatus(int $id, string $status)
    {
        return $this->programModel
            ->where('id', $id)
            ->update(['status' => $status]);
    }

    public function getAllPrograms(): array
    {
        $sql = "
        SELECT 
            p.id AS program_id,
            p.slug,
            p.name,
            p.description,
            p.image_url,
            p.shortname,
            p.status,
            p.sponsor,
            p.age_groups,
            pc.course_id,
            l.title AS course_title
        FROM programs p
        LEFT JOIN program_courses pc 
            ON pc.program_id = p.id 
            AND pc.is_active = 1
        LEFT JOIN learning_courses l 
            ON l.id = pc.course_id
        ORDER BY p.created_at DESC, pc.display_order ASC
    ";

        $rows = $this->programModel
            ->rawQuery($sql);

        $programs = [];

        foreach ($rows as $row) {
            $pid = $row['program_id'];

            if (!isset($programs[$pid])) {
                $programs[$pid] = [
                    'id' => $pid,
                    'slug' => $row['slug'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'image_url' => $row['image_url'],
                    'shortname' => $row['shortname'],
                    'status' => $row['status'],
                    'sponsor' => $row['sponsor'],
                    'age_groups' => json_decode($row['age_groups'] ?? '{}', true),
                    'courses' => [],
                ];
            }

            if ($row['course_id']) {
                $programs[$pid]['courses'][] = [
                    'id' => $row['course_id'],
                    'title' => $row['course_title'],
                ];
            }
        }

        return array_values($programs);
    }

    public function deleteProgram(int $id)
    {
        $course = $this->programCourseModel
            ->where('program_id', $id)
            ->first();

        if (!empty($course)) {
            throw new \Exception("Cannot delete program with associated courses.");
        }

        $program = $this->programModel
            ->where('id', $id)
            ->first();

        if ($program && !empty($program['image_url'])) {
            StorageService::deleteFile($program['image_url']);
        }

        return $this->programModel
            ->where('id', $id)
            ->delete();
    }

    private function toSlug(string $text): string
    {
        return strtolower(trim((string)preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }
}
