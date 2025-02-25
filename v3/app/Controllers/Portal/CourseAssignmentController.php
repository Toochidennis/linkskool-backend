<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\CourseAssignment;
use V3\App\Traits\PermissionTrait;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\ResponseHandler;

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
        $data = $this->validateData($this->post, $this->courseAssignment::INSERT_REQUIRED);

        try {
            $assignmentId = $this->courseAssignment->assignCourse($data);
            $this->response = ['success' => true, 'message' => 'Course assigned successfully', 'id' => $assignmentId];
        } catch (Exception $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAssignments(array $params)
    {
        $data = $this->validateData($params, $this->courseAssignment::GET_REQUIRED);

        try {
            $assignments = $this->courseAssignment->getAssignedCourses(
                columns: [
                    'staff_course_table.*',
                    'class_table.id',
                    'class_table.class_name',
                    'class_table.level',
                    'course_table.id',
                    'course_table.course_name',
                    'course_table.course_code',
                    'level_table.level_name'
                ],
                joins: [
                    [
                        'table' => 'course_table',
                        'condition' => 'staff_course_table.course = course_table.id',
                        'type' => 'INNER'
                    ],
                    [
                        'table' => 'class_table',
                        'condition' => 'staff_course_table.class = class_table.id',
                        'type' => 'INNER'
                    ],
                    [
                        'table' => 'level_table',
                        'condition' => 'class_table.level = level_table.id',
                        'type' => 'INNER'
                    ]
                ],
                conditions: [
                    'ref_no' => $data['ref_no'],
                    'year' => $data['year'],
                    'term'=> $data['term']
                ]
            );

            $this->response = ['success'=>true, 'assigned_courses'=>$assignments];
        } catch (Exception $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function deleteAssignment() {}
}
