<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\ClassService;

class ClassController extends BaseController
{
    private ClassService $classService;

    public function __construct()
    {
        parent::__construct();
        $this->classService = new ClassService($this->pdo);
    }

    public function addClass()
    {
        $data = $this->validateData(data: $this->post, requiredFields: ['class_name', 'level_id']);

        try {
            $classId = $this->classService->insertClass(data: $data);

            if ($classId) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Class added successfully.',
                    'class_id' => $classId
                ], HttpStatus::CREATED);
            }

            return $this->respondError('Failed to add class', HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateClass()
    {
        //TODO:
    }

    public function getClasses()
    {
        try {
            return $this->respond([
                'success' => true,
                'response' => $this->classService->fetchClasses()
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
