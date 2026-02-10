<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\NewsCategory;

class NewsCategoryService
{
    private NewsCategory $newsCategoryModel;

    public function __construct(\PDO $pdo)
    {
        $this->newsCategoryModel = new NewsCategory($pdo);
    }

    public function addCategory(array $data): bool|array
    {
        $id = $this->newsCategoryModel->insert($data);

        if (!$id) {
            return false;
        }
        return $this->newsCategoryModel->where('id', $id)->first();
    }

    public function getCategories(): array
    {
        return $this->newsCategoryModel->get();
    }
}
