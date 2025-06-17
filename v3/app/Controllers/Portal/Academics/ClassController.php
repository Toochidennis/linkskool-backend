<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Models\Portal\ClassModel;
use V3\App\Controllers\BaseController;

class ClassController extends BaseController
{
    private ClassModel $classModel;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->classModel = new ClassModel(pdo: $this->pdo);
    }

    public function addClass()
    {
        $requiredFields = ['class_name', 'level'];
        $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);

        try {
            $classId = $this->classModel->insert(data: $data);

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
            $results = $this->classModel->get();

            foreach ($results as &$result) {
                $result['level_id'] = $result['level'];
                unset($result['level']);
            }

            return $this->respond(['success' => true, 'response' => $results]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
