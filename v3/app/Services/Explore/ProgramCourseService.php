<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\ProgramCourse;

class ProgramCourseService
{
    protected ProgramCourse $programCourseModel;

    public function __construct(\PDO $pdo)
    {
        $this->programCourseModel = new ProgramCourse($pdo);
    }

    public function addCourseToProgram(array $data)
    {
        if (!isset($_FILES['image'])) {
            throw new \Exception("Invalid image upload.");
        }

        $data['image_url'] = StorageService::saveFile($_FILES['image']);

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));

        $payload = [
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'],
            'image_url' => $data['image_url'],
            'slogan' => $data['slogan'],
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'status' => $data['status'],
            'age_groups' => json_encode($data['age_groups']),
            'program_id' => $data['program_id'],
        ];

        return $this->programCourseModel->insert($payload);
    }

    public function updateProgramCourse(array $data)
    {
        if (isset($_FILES['image'])) {
            $data['image_url'] = StorageService::saveFile($_FILES['image']);
        }

        if ($data['title']) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
        }

        $payload = [
            'slug' => $slug ?? null,
            'title' => $data['title'],
            'description' => $data['description'],
            'image_url' => $data['image_url'] ?? null,
            'slogan' => $data['slogan'],
            'status' => $data['status'],
            'age_groups' => json_encode($data['age_groups'] ?? []),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $id = $this->programCourseModel
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

    public function updateCourseStatus(int $id, string $status)
    {
        return $this->programCourseModel
            ->where('id', $id)
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function getCoursesByProgramId(int $programId)
    {
        $result =  $this->programCourseModel
            ->where('program_id', $programId)
            ->orderBy('created_at', 'DESC')
            ->get();

        return array_map(function ($course) {
            $course['age_groups'] = json_decode($course['age_groups'], true);
            return $course;
        }, $result);
    }

    public function deleteProgramCourse(int $id)
    {
        $course = $this->programCourseModel
            ->where('id', $id)
            ->first();

        if ($course && !empty($course['image_url'])) {
            StorageService::deleteFile($course['image_url']);
        }

        return $this->programCourseModel
            ->where('id', $id)
            ->delete();
    }
}
