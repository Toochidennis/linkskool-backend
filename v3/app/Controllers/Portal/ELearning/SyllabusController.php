<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
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
        $data = $this->validateData(
            data: $this->post,
            requiredFields: [
                'title',
                'description',
                'course_id',
                'course_name',
                'level_id',
                'classes',
                'classes.*.id',
                'classes.*.name',
                'creator_id',
                'creator_name',
                'term'
            ]
        );

        try {
            $newId = $this->service->create($data);

            if ($newId) {
                return $this->respond(
                    data: [
                        'success' => true,
                        'syllabusId' => $newId,
                        'message' => 'Syllabus created successfully.'
                    ],
                    statusCode: HttpStatus::CREATED
                );
            }
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function get(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['term', 'level_id']);

        try {
            return $this->respond(
                data: [
                    'success' => true,
                    'response' => $this->service->getSyllabus(filters: $data)
                ]
            );
        } catch (Exception $e) {
            return $this->respondError(message: $e->getMessage());
        }
    }
}
