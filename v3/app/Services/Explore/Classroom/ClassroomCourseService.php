<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Common\Utilities\Str;
use V3\App\Common\Utilities\Uuid;
use V3\App\Models\Explore\Classroom\ClassroomCourse;
use V3\App\Services\Explore\StorageService;

class ClassroomCourseService
{
    private ClassroomCourse $classroomCourse;

    public function __construct(\PDO $pdo)
    {
        $this->classroomCourse = new ClassroomCourse($pdo);
    }

    public function createCourse(array $data): bool
    {
        $courseSlug = Str::slug($data['name']);

        if (isset($_FILES['image'])) {
            $data['image_url'] = StorageService::saveFile(
                $_FILES['image'],
                "explore/classrooms/courses/{$courseSlug}/image"
            );
        }

        $payload = [
            'slug' => Uuid::v4(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'institution_id' => $data['institution_id'],
            'image_url' => $data['image_url'] ?? null,
            'created_by' => $data['created_by'],
            'subject_id' => $data['subject_id'] ?? null,
            'level_id' => $data['level_id'] ?? null,
            'duration' => $data['duration'] ?? null,
            'pricing_type' => $data['pricing_type'] ?? 'free',
            'price' => $data['price'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'status' => $data['status'],
        ];

        return $this->classroomCourse->insert($payload);
    }

    public function getCoursesByInstitution(int $institutionId): array
    {
        $rows = $this->classroomCourse->where('institution_id', $institutionId)->get();

        return array_map(function (array $row): array {
            $row['image_url'] = AssetUrl::fromAppUrl($row['image_url'] ?? null);
            return $row;
        }, $rows);
    }
}
