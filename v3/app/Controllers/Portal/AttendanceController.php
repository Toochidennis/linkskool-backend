<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Attendance;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;

class AttendanceController extends BaseController
{
    private Attendance $attendance;
    use ValidationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->attendance = new Attendance($this->pdo);
    }

    public function addAttendance()
    {

        $requiredFields = [
            'year',
            'term',
            'staff_id',
            'count',
            'class',
            'course',
            'register',
            'date'
        ];
        
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $attendanceId = $this->attendance->insert($data);

            $this->response = $attendanceId ? [
                'success' => true,
                'message' => 'Attendance added successfully.',
                'assessment_id' => $attendanceId
            ] : [
                'success' => false,
                'message' => 'Failed to add attendance'
            ];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateAttendance()
    {
        $requiredFields = [
            'id',
            'year',
            'term',
            'staff_id',
            'count',
            'class',
            'course',
            'register',
            'date'
        ];

        $data = $this->validateData($this->post,  $requiredFields);

        try {
            $isUpdated = $this->attendance
                ->where('id', '=', $data['id'])
                ->update($data);

            $this->response = $isUpdated ?
                ['success' => true, 'message' => 'Attendance updated successfully']
                : ['success' => false, 'message' => 'Failed to update attendance'];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    /**
     * Returns attendance data based on provided filters.
     *
     * Expected query parameters:
     * - date
     * - class
     * - course_id
     *
     * @param array $params
     */
    public function getAttendance(array $params)
    {
        $requiredFields = ['course', 'class', 'date'];
        $data = $this->validateData($params, $requiredFields);

        try {
            $result =  $this->attendance
                ->select(columns: ['id', 'count', 'date', 'register'])
                ->where('class', '=', $data['class'])
                ->where('course', '=', $data['course'])
                ->where('date', '=', $data['date'])
                ->get();

            $this->response = ['success' => true, 'attendance_records' => $result];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }
        ResponseHandler::sendJsonResponse($this->response);
    }


    /**
     * Returns summary of  attendance data.
     *
     *  * Expected query parameters:
     * - class
     * - course
     * - term
     * - year
     * 
     * @param array $params
     * @return void
     */
    public function getAttendanceSummary($params)
    {
        $requiredFields = ['course', 'class', 'term', 'year'];
        $data = $this->validateData($params, $requiredFields);

        try {
            $result = $this->attendance
                ->select(columns: ['id', 'count', 'date'])
                ->where('class', '=', $data['class'])
                ->where('course', '=', $data['course'])
                ->where('year', '=', $data['year'])
                ->where('term', '=', $data['term'])
                ->get();

            $this->response = ['success' => true, 'attendance_records' => $result];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAttendanceById(array $params)
    {
        $data = $this->validateData($params, ['id']);

        try {
            $result = $this->attendance
                ->select(columns: ['id', 'count', 'date', 'register'])
                ->where('id', '=', $data['id'])
                ->get();

            $this->response = ['success' => true, 'attendance_records' => $result];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }
        ResponseHandler::sendJsonResponse($this->response);
    }
}
