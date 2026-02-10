<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\LearningCourseService;

#[Route('/public')]
class LearningCourseController extends ExploreBaseController
{
    private LearningCourseService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new LearningCourseService($this->pdo);
    }

    #[Route('/learn/courses', 'POST', ['api', 'auth'])]
    public function create(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'slogan' => 'required|string|max:255',
                'author_name' => 'required|string|max:255',
                'author_id' => 'required|integer',

                'image' => 'required|array',
                'image.name' => 'required|string',
                'image.tmp_name' => 'required|string',
                'image.error' => 'required|integer',
                'image.size' => 'required|integer|max:5242880' // 5 MB
            ]
        );

        $id =  $this->service->create($validatedData);

        if (!$id) {
            $this->respondError(
                'Failed to create course.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Course created successfully.',
                'id' => $id
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/learn/courses/{id}', 'POST', ['api', 'auth'])]
    public function update(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'slogan' => 'required|string|max:255',

                'image' => 'nullable|array',
                'image.name' => 'required_with:image|string',
                'image.tmp_name' => 'required_with:image|string',
                'image.error' => 'required_with:image|integer',
                'image.size' => 'required_with:image|integer'
            ]
        );

        $updated =  $this->service->update($validatedData);

        if (!$updated) {
            $this->respondError(
                'Failed to update course.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Course updated successfully.',
            ],
            HttpStatus::OK
        );
    }


    #[Route('/learn/courses', 'GET', ['api', 'auth'])]
    public function getCourses()
    {
        $courses = $this->service->get();

        $this->respond(
            [
                'success' => true,
                'data' => $courses
            ],
            HttpStatus::OK
        );
    }

    #[Route('/learn/courses/program/{id}', 'GET', ['api', 'auth'])]
    public function getCoursesByProgramId(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'id' => 'required|integer',
            ]
        );

        $courses = $this->service
            ->getCoursesByProgramId((int)$validatedData['id']);

        $this->respond(
            [
                'success' => true,
                'data' => $courses
            ],
            HttpStatus::OK
        );
    }

    #[Route('/learn/courses/{id}', 'DELETE', ['api', 'auth'])]
    public function deleteCourse(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'id' => 'required|integer',
            ]
        );

        $deleted = $this->service
            ->deleteCourse((int)$validatedData['id']);

        if (!$deleted) {
            $this->respondError(
                'Failed to delete learning course.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Learning course deleted successfully.'
            ],
            HttpStatus::OK
        );
    }
}
