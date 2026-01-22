<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\LearningCourse;
use V3\App\Models\Explore\Program;

class LearningCourseService
{
    private LearningCourse $learningCourseModel;
    private Program $programModel;

    public function __construct(\PDO $pdo)
    {
        $this->learningCourseModel = new LearningCourse($pdo);
        $this->programModel = new Program($pdo);
    }

    public function create(array $data)
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
        ];

        return $this->learningCourseModel->insert($payload);
    }

    public function update(array $data)
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
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $id = $this->learningCourseModel
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

    public function get()
    {
        return $this->learningCourseModel
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getCoursesByProgramId(int $programId): array
    {
        return $this->learningCourseModel
            ->select([
                'learning_courses.id',
                'learning_courses.title',
                'learning_courses.slug',
                'learning_courses.description',
                'learning_courses.image_url',
                'learning_courses.created_at',
                'learning_courses.slogan',
            ])
            ->join(
                'program_courses',
                'learning_courses.id = program_courses.course_id'
            )
            ->where('program_courses.program_id', $programId)
            ->where('program_courses.is_active', 1)
            ->orderBy('program_courses.display_order', 'ASC')
            ->get();
    }

    public function deleteCourse(int $id)
    {
        $result = $this->programModel
            ->where('course_id', $id)
            ->first();

        if (!empty($result)) {
            throw new \Exception("Cannot delete learning course associated with existing programs.");
        }

        $course = $this->learningCourseModel
            ->where('id', $id)
            ->first();

        if ($course && !empty($course['image_url'])) {
            StorageService::deleteFile($course['image_url']);
        }

        return $this->learningCourseModel
            ->where('id', $id)
            ->delete();
    }
}
