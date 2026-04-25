<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\StudyCategoryService;

#[Group('/public/cbt/study')]
class StudyCategoryController extends ExploreBaseController
{
    private StudyCategoryService $studyCategoryService;

    public function __construct()
    {
        parent::__construct();
        $this->studyCategoryService = new StudyCategoryService($this->pdo);
    }

    #[Route('/categories', 'POST', ['api', 'auth', 'role:admin'])]
    public function storeCategory(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'title' => 'required|string|filled',
            'course_id' => 'required|integer|min:1',
            'course_name' => 'required|string|filled',
            'description' => 'nullable|string',
        ]);

        $categoryId = $this->studyCategoryService->addCategory($data);

        if ($categoryId <= 0) {
            $this->respondError('Failed to create category.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Category created successfully.',
            'category_id' => $categoryId,
        ]);
    }

    #[Route('/categories/{id:\d+}', 'PUT', ['api', 'auth', 'role:admin'])]
    public function updateCategory(array $vars): void
    {
        $data = $this->validate([...$this->getRequestData(), ...$vars], [
            'id' => 'required|integer|min:1',
            'title' => 'required|string|filled',
            'course_id' => 'required|integer|min:1',
            'course_name' => 'required|string|filled',
            'description' => 'nullable|string',
        ]);

        $updated = $this->studyCategoryService->updateCategory($data['id'], $data);

        if (!$updated) {
            $this->respondError('Failed to update category.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Category updated successfully.',
        ]);
    }

    #[Route('/categories', 'GET', ['api'])]
    public function getAllCategories(): void
    {
        $this->respond([
            'success' => true,
            'data' => $this->studyCategoryService->getAllCategories(),
        ]);
    }

    #[Route('/courses/{course_id:\d+}/categories', 'GET', ['api'])]
    public function getCategoriesByCourseId(array $vars): void
    {
        $data = $this->validate($vars, [
            'course_id' => 'required|integer|min:1',
        ]);

        $this->respond([
            'success' => true,
            'data' => $this->studyCategoryService->getCategoryByCourseId($data['course_id']),
        ]);
    }

    #[Route('/categories/{id:\d+}', 'DELETE', ['api', 'auth', 'role:admin'])]
    public function deleteCategory(array $vars): void
    {
        $data = $this->validate($vars, [
            'id' => 'required|integer|min:1',
        ]);

        $deleted = $this->studyCategoryService->deleteCategory($data['id']);

        if (!$deleted) {
            $this->respondError('Failed to delete category.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}
