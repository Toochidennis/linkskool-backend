<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Portal\ELearning\SyllabusService;

class SyllabusController extends BaseController
{
    private SyllabusService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SyllabusService($this->pdo);
    }

    public function store()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'title' => 'required|string|filled',
                'description' => 'required|string|filled',
                'course_id' => 'required|integer',
                'course_name' => 'required|string|filled',
                'level_id' => 'required|integer',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled',
                'creator_id' => 'required|integer',
                'creator_name' => 'required|string|filled',
                'term' => 'required|integer'
            ]
        );

        try {
            $newId = $this->service->create($data);

            if ($newId > 0) {
                $this->respond(
                    data: [
                        'success' => true,
                        'syllabusId' => $newId,
                        'message' => 'Syllabus created successfully.'
                    ],
                    statusCode: HttpStatus::CREATED
                );
            }

            $this->respondError('Failed to create syllabus', HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        $data = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'title' => 'required|string|filled',
                'description' => 'required|string|filled',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled'
            ]
        );

        try {
            $id = $this->service->updateSyllabus($data);

            if ($id > 0) {
                $this->respond(
                    data: [
                        'success' => true,
                        'message' => 'Syllabus updated successfully.',
                        'syllabusId' => $id
                    ]
                );
            }

            return $this->respondError('No changes', HttpStatus::INFO);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function get(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'term' => 'required|integer',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer'
            ]
        );

        try {
            $this->respond(
                data: [
                    'success' => true,
                    'response' => $this->service->getSyllabus(filters: $data)
                ]
            );
        } catch (Exception $e) {
            $this->respondError(message: $e->getMessage());
        }
    }
}
