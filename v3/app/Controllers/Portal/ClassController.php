<?php

namespace V3\App\Controllers\Portal;

use V3\App\Models\Portal\ClassModel;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Utilities\DatabaseConnector;
use V3\App\Services\Portal\AuthService;
use V3\App\Services\Portal\ClassService;

class ClassController{
    private array $response = ['success' => false, 'message' => ''];
    private array $post;
    private ClassModel $classModel;
    private ClassService $classService;

    public function __construct(){
        $this->initialize();
    }

    private function initialize()
    {
        AuthService::verifyAPIKey();
        AuthService::verifyJWT();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = DataExtractor::extractPostData();
            $dbname = $this->post['_db'] ?? '';
        } else {
            $dbname = $_GET['_db'] ?? '';
        }

        if (!empty($dbname)) {
            $db = DatabaseConnector::connect(dbname: $dbname);
            $this->classModel = new ClassModel(pdo: $db);
        } else {
            $this->response['message'] = '_db is required.';
            http_response_code(401);
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        $this->classService = new ClassService();
    }

    public function addClass()
    {
        try {
            $data = $this->classService->validateAndGetData(post: $this->post);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

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
            }else{
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