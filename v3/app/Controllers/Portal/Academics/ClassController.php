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
        $data = $this->validate(
            data: $this->post,
            rules: [
                'class_name' => 'required|string|filled',
                'level_id' => 'required|integer|filled',
                'result_template' => 'sometimes|string',
                'form_teacher_ids' => 'sometimes|array'
            ]
        );

        try {
            $classId = $this->classService->insertClass(data: $data);

            if ($classId) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Class added successfully.',
                    'class_id' => $classId
                ], HttpStatus::CREATED);
            }

            return $this->respondError(
                'Failed to add class',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }


    public function updateClass(array $vars)
    {
        $data = $this->validate(
            data: array_merge($this->post, $vars),
            rules: [
                'id' => 'required|integer|filled',
                'class_name' => 'required|string|filled',
                'level_id' => 'required|integer|filled',
                'result_template' => 'sometimes|string',
                'form_teacher_ids' => 'sometimes|array'
            ]
        );

        try {
            $updated = $this->classService->updateClass($data);

            if ($updated) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Class updated successfully.'
                ], HttpStatus::OK);
            }

            return $this->respondError(
                'Failed to update class',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getClasses()
    {
        try {
            return $this->respond([
                'success' => true,
                'data' => $this->classService->fetchClasses()
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function deleteClass(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer|filled'
            ]
        );

        try {
            $deleted = $this->classService->deleteClass($data['id']);

            if ($deleted) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Class deleted successfully.'
                ], HttpStatus::OK);
            }

            return $this->respondError(
                'Failed to delete class',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
