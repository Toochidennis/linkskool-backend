<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Common\Utilities\Str;
use V3\App\Common\Utilities\Uuid;
use V3\App\Models\Explore\Classroom\ClassroomCourse;
use V3\App\Models\Explore\Classroom\ClassroomStudent;
use V3\App\Services\Explore\StorageService;

class ClassroomCourseService
{
    private ClassroomCourse $classroomCourse;
    private ClassroomStudent $classroomStudent;

    public function __construct(\PDO $pdo)
    {
        $this->classroomCourse = new ClassroomCourse($pdo);
        $this->classroomStudent = new ClassroomStudent($pdo);
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
            'join_code' => $data['join_code'],
        ];

        return $this->classroomCourse->insert($payload);
    }

    public function getCoursesByInstitution(
        int $institutionId,
        array $filters = [],
        int $page = 1,
        int $limit = 20
    ): array {
        $query = $this->classroomCourse
            ->select([
                'classroom_courses.*',
                'course_table.course_name AS subject_name',
            ])
            ->join('course_table', 'classroom_courses.subject_id = course_table.id', 'LEFT')
            ->where('classroom_courses.institution_id', $institutionId);

        if (!empty($filters['level_id'])) {
            $query->where('classroom_courses.level_id', $filters['level_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('classroom_courses.status', $filters['status']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('classroom_courses.subject_id', $filters['subject_id']);
        }

        $result = $query->paginate($page, $limit);

        $result['data'] = array_map(function (array $row): array {
            $row['image_url']    = AssetUrl::fromAppUrl($row['image_url'] ?? null);
            $row['subject_name'] = $row['subject_name'] ? ucwords(strtolower($row['subject_name'])) : null;
            return $row;
        }, $result['data']);

        return $result;
    }

    public function listStudents(
        int $institutionId,
        array $filters = [],
        int $page = 1,
        int $limit = 20
    ): array {
        $query = $this->classroomStudent
            ->select([
                'classroom_students.*',
                'level.name as level_name',
            ])
            ->join('level', 'classroom_students.level_id = level.id', 'LEFT')
            ->where('classroom_students.institution_id', $institutionId);

        if (!empty($filters['level_id'])) {
            $query->where('classroom_students.level_id', $filters['level_id']);
        }

        if (!empty($filters['name'])) {
            $term = '%' . $filters['name'] . '%';
            $query->whereRaw(
                '(`first_name` LIKE ? OR `last_name` LIKE ? OR `middle_name` LIKE ?)',
                [$term, $term, $term]
            );
        }

        if (!empty($filters['reg_number'])) {
            $query->where('reg_number', 'LIKE', '%' . $filters['reg_number'] . '%');
        }

        return $query->paginate($page, $limit);
    }

    public function updateCourseStatus(int $id, string $status): bool
    {
        return $this->classroomCourse
            ->where('id', $id)
            ->update([
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function updateCourse(int $id, array $data): bool
    {
        if (isset($_FILES['image'])) {
            $courseSlug = Str::slug($data['name'] ?? (string) $id);
            $data['image_url'] = StorageService::saveFile(
                $_FILES['image'],
                "explore/classrooms/courses/{$courseSlug}/image"
            );
        }

        $payload = [
            'name'  => $data['name'],
            'description'    => $data['description'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'level_id' => $data['level_id'] ?? null,
            'duration'  => $data['duration'] ?? null,
            'pricing_type'   => $data['pricing_type'] ?? 'free',
            'price'          => $data['price'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'status'         => $data['status'],
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['image_url'])) {
            $payload['image_url'] = $data['image_url'];

            if (!empty($data['old_image_url'])) {
                StorageService::deleteFile($data['old_image_url']);
            }
        }

        return $this->classroomCourse->where('id', $id)->update($payload);
    }
}
