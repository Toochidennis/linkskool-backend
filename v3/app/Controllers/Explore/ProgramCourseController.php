<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProgramCourseService;

#[Group('/public/programs/{program_id}/courses')]
class ProgramCourseController extends ExploreBaseController
{
    private ProgramCourseService $programCourseService;

    public function __construct()
    {
        parent::__construct();
        $this->programCourseService = new ProgramCourseService($this->pdo);
    }

    #[Route('', 'POST', ['api', 'auth'])]
    public function create(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'slogan' => 'required|string|max:255',
                'status' => 'required|string|in:draft,published,archived',
                'age_groups' => 'required|array',
                'age_groups.*.min' => 'integer',
                'age_groups.*.max' => 'integer',
                'program_id' => 'required|integer',
                'author_name' => 'required|string|max:255',
                'author_id' => 'required|integer',

                'image' => 'required|array',
                'image.name' => 'required|string',
                'image.tmp_name' => 'required|string',
                'image.error' => 'required|integer',
                'image.size' => 'required|integer'
            ]
        );

        $id =  $this->programCourseService->addCourseToProgram($validatedData);

        if (!$id) {
            $this->respondError(
                'Failed to create program course.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program course created successfully.',
                'id' => $id
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{id}', 'POST', ['api', 'auth'])]
    public function update(array $vars)
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'slogan' => 'required|string|max:255',
                'status' => 'required|string|in:draft,published,archived',
                'age_groups' => 'nullable|array',
                'age_groups.*.min' => 'integer',
                'age_groups.*.max' => 'integer',
                'updated_by' => 'nullable|integer',

                'image' => 'nullable|array',
                'image.name' => 'required_with:image|string',
                'image.tmp_name' => 'required_with:image|string',
                'image.error' => 'required_with:image|integer',
                'image.size' => 'required_with:image|integer'
            ]
        );

        $updated =  $this->programCourseService->updateProgramCourse($validatedData);

        if (!$updated) {
            $this->respondError(
                'Failed to update program course.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program course updated successfully.',
            ],
            HttpStatus::OK
        );
    }

    #[Route('', 'GET', ['api', 'auth'])]
    public function getCoursesByProgramId(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'program_id' => 'required|integer',
            ]
        );

        $courses = $this->programCourseService
            ->getCoursesByProgramId((int)$validatedData['program_id']);

        $this->respond(
            [
                'success' => true,
                'data' => $courses
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}', 'DELETE', ['api', 'auth'])]
    public function deleteCourse(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'id' => 'required|integer',
            ]
        );

        $deleted = $this->programCourseService
            ->deleteProgramCourse((int)$validatedData['id']);

        if (!$deleted) {
            $this->respondError(
                'Failed to delete program course.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Program course deleted successfully.'
            ],
            HttpStatus::OK
        );
    }
}
