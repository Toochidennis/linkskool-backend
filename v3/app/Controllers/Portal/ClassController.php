<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Traits\ValidationTrait;
use V3\App\Models\Portal\ClassModel;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;

class ClassController extends BaseController
{
    private ClassModel $classModel;
    use ValidationTrait;

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
            $success = $this->classModel->insert(data: $data);
            $this->response = [
                'success' => true,
                'message' => 'class added successfully.',
                'class_id' => $success
            ];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateClass() {}

    public function getClasses()
    {
        try {
            $result = $this->classModel->get();

            $classes  = array_map(fn($row) => [
                'id' => $row['id'],
                'class_name' => $row['class_name'],
                'level' => $row['level'],
                'form_teacher' => $row['form_teacher']
            ], $result);

            $this->response = ['success' => true, 'classes' => $classes];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getClassById() {}

    public function deleteClass() {}
}
