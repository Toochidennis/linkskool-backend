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
    use ValidationTrait;
    use PermissionTrait;

    private Result $result;

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

    public function getResultTermsByStudent(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);

        try {
            $terms = $this->result
                ->select(
                    columns: [
                    'reg_no',
                    'class class_id',
                    'year',
                    'term',
                    "avg(result_table.total) AS average_score"
                    ]
                )
                ->where('reg_no', $data['id'])
                ->where('total', 'IS  NOT', null)
                ->groupBy(['class', 'year', 'term'])
                ->orderBy(['year' => 'DESC', 'term' => 'ASC'])
                ->get();

            $structured = [];

            foreach ($terms as $row) {
                $year = $row['year'];

                if (!isset($structured[$year])) {
                    $structured[$year] = ['terms' => []];
                }

                $structured[$year]['terms'][] = [
                    'term' => (int) $row['term'],
                    'average_score' => number_format((float)$row['average_score'], 2)
                ];
            }

            $this->response = ['success' => true, 'result_terms' => $structured];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }
        ResponseHandler::sendJsonResponse($this->response);
    }
}
