<?php

namespace V3\App\Services\Explore;

use  V3\App\Models\Explore\StudyCategory;

class StudyCategoryService
{
    private StudyCategory $studyCategory;

    public function __construct(\PDO $pdo)
    {
        $this->studyCategory = new StudyCategory($pdo);
    }

    public function addCategory(array $data): int
    {
        return $this->studyCategory->insert([
            'title' => $data['title'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function updateCategory(int $categoryId, array $data): bool
    {
        return $this->studyCategory
            ->where('id', $categoryId)
            ->update([
                'title' => $data['title'],
                'course_id' => $data['course_id'],
                'course_name' => $data['course_name'],
                'description' => $data['description'] ?? null,
            ]);
    }

    public function getCategoryByCourseId(int $courseId): ?array
    {
        return $this->studyCategory
            ->where('course_id', $courseId)
            ->get();
    }

    public function getAllCategories(): array
    {
        return $this->studyCategory
            ->get();
    }

    public function deleteCategory(int $categoryId): bool
    {
        return $this->studyCategory
            ->where('id', $categoryId)
            ->delete();
    }
}
