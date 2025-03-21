<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Traits\PermissionTrait;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\CourseAssignment;

class CourseAssignmentController extends BaseController
{
    use ValidationTrait;
    use PermissionTrait;

    private CourseAssignment $courseAssignment;

    public function __construct()
    {
        parent::__construct();
        $this->courseAssignment = new CourseAssignment($this->pdo);
    }

    public function createAssignment()
    {
        $requiredFields = ['year', 'term', 'ref_no', 'class', 'course'];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $assignmentId = $this->courseAssignment->insert($data);

            $this->response = $assignmentId ?
                [
                    'success' => true,
                    'message' => 'Course assigned successfully',
                ] :
                [
                    'success' => false,
                    'message' => 'Failed to assign course',
                ];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAssignments(array $params)
    {
        $requiredFields = ['year', 'term', 'ref_no'];
        $data = $this->validateData($params, $requiredFields);

        try {
            $assignments = $this->courseAssignment
                ->select(
                    columns: [
                        'staff_course_table.*',
                        'course_table.id',
                        'course_table.course_name',
                        'course_table.course_code',
                        'class_table.id',
                        'class_table.class_name',
                        'class_table.level',
                        'level_table.level_name'
                    ]
                )
                ->join('course_table', 'staff_course_table.course = course_table.id')
                ->join('class_table', 'staff_course_table.class = class_table.id')
                ->join('level_table', 'class_table.level = level_table.id')
                ->where('staff_course_table.ref_no', '=', $data['ref_no'])
                ->where('staff_course_table.year', '=', $data['year'])
                ->where('staff_course_table.term', '=', $data['term'])
                ->get();

            $this->response = ['success' => true, 'assigned_courses' => $assignments];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function deleteAssignment() {}
}
