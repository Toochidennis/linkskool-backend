<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\NewsCategoryService;

#[Group('/public/news')]
class NewsCategoryController extends ExploreBaseController
{
    private NewsCategoryService $newsCategoryService;

    public function __construct()
    {
        parent::__construct();
        $this->newsCategoryService = new NewsCategoryService($this->pdo);
    }

    #[Route("/categories", 'POST', ['api', 'auth'])]
    public function addCategory(): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'name' => 'required|string|max:100|unique:explore_news_category,name',
                'description' => 'nullable|string|max:255',
            ]
        );

        $res = $this->newsCategoryService->addCategory($data);

        if (!$res) {
            $this->respondError(
                "Failed to create news category.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status' => true,
                'message' => 'News category added successfully',
                'id' => $res
            ],
            HttpStatus::CREATED
        );
    }

    #[Route("/categories", 'GET', ['api', 'auth'])]
    public function getCategories(): void
    {
        $categories = $this->newsCategoryService->getCategories();

        $this->respond(
            [
                'status' => true,
                'data' => $categories
            ]
        );
    }
}
