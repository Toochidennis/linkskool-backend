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

    public function createProgram(array $data): bool|int
    {
        if (!isset($_FILES['image'])) {
            throw new \Exception("Invalid image upload.");
        }
        $data['image_url'] = StorageService::saveFile($_FILES['image']);

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));

        $payload = [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'image_url' => $data['image_url'],
            'author_name' => $data['author_name'],
            'author_id' => $data['author_id'],
            'shortname' => $data['shortname'],
            'status' => $data['status'],
            'sponsor' => $data['sponsor'] ?? null,
        ];

        return $this->programModel->insert($payload);
    }

    public function updateProgram(array $data): bool
    {
        if (isset($_FILES['image'])) {
            $data['image_url'] = StorageService::saveFile($_FILES['image']);
        }

        $payload = [
            'name' => $data['name'],
            'description' => $data['description'],
            'image_url' => $data['image_url'] ?? null,
            'shortname' => $data['shortname'],
            'status' => $data['status'],
            'sponsor' => $data['sponsor'] ?? null,
        ];

        $id = $this->programModel
            ->where('id', $data['id'])
            ->update(
                array_filter(
                    $payload,
                    fn($value) => $value !== null
                )
            );

        if (!empty($data['old_image_url']) && isset($data['image_url'])) {
            StorageService::deleteFile($data['old_image_url']);
        }

        return $id;
    }

    public function updateStatus(int $id, string $status)
    {
        return $this->programModel
            ->where('id', $id)
            ->update(['status' => $status]);
    }

    public function getAllPrograms(): array
    {
        return $this->programModel
            ->select([
                'programs.id',
                'programs.slug',
                'programs.name',
                'programs.description',
                'programs.image_url',
                'programs.shortname',
                'programs.status',
                'programs.sponsor',
                'COUNT(program_courses.id) AS course_count'
            ])
            ->join('program_courses', 'programs.id = program_courses.program_id', 'LEFT')
            ->groupBy('programs.id')
            ->orderBy('programs.created_at', 'DESC')
            ->get();
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
}
