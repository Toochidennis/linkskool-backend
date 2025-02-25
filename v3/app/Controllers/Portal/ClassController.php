<?php

namespace V3\App\Controllers\Portal;

use V3\App\Traits\ValidationTrait;
use V3\App\Models\Portal\ClassModel;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ClassService;

class ClassController extends BaseController
{

    private ClassModel $classModel;
    private ClassService $classService;
    use ValidationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->classModel = new ClassModel(pdo: $this->pdo);
        $this->classService = new ClassService();
    }

    public function addClass()
    {
        $requiredFields = ['class_name', 'level'];

        $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);

        try {
            $success = $this->classModel->insertClass(data: $data);
            $this->response = [
                'success' => true,
                'message' => 'class added successfully.',
                'class_id' => $success
            ];
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateClass() {}

    public function getClasses()
    {
        try {
            $result = $this->classModel->getClasses();

            if ($result) {
                $classes  = array_map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'class_name' => $row['class_name'],
                        'level' => $row['level'],
                        'form_teacher' => $row['form_teacher']
                    ];
                }, $result);

                $this->response = ['success' => true, 'classes' => $classes];
            } else {
                $this->response = ['success' => true, 'classes' => []];
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getClassById() {}

    public function deleteClass() {}
}
