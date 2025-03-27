<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Models\Portal\Result;
use V3\App\Traits\PermissionTrait;
use V3\App\Traits\ValidationTrait;
use V3\App\Controllers\BaseController;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;

class ResultController extends BaseController
{
    private Result $result;
    use ValidationTrait;
    use PermissionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->result = new Result($this->pdo);
    }

    public function addResult()
    {
        $requiredFields = ['year', 'class_id', 'term', 'course_id', 'student_grades'];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $update = $this->
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }
}
