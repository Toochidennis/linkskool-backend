<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Attendance;
use V3\App\Traits\ValidationTrait;
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
        $data = $this->validateData($this->post, $this->attendance::INSERT_REQUIRED);

        try {
            $attendanceId = $this->attendance->insertAttendance($data);

            $this->response = $attendanceId ? [
                'success' => true,
                'message' => 'Attendance added successfully.',
                'assessment_id' => $attendanceId
            ] : [
                'success' => false,
                'message' => 'Failed to add attendance'
            ];
        } catch (Exception $e) {
            http_response_code(response_code: 500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateAttendance()
    {
        $this->attendance::INSERT_REQUIRED[] = 'id';
        $data = $this->validateData($this->post,  $this->attendance::INSERT_REQUIRED);

        try {
            $isUpdated = $this->attendance->updateAttendance($data, ['id' => $data['id']]);

            $this->response = $isUpdated ?
                ['success' => true, 'message' => 'Attendance updated successfully']
                : ['success' => false, 'message' => 'Failed to update attendance'];
        } catch (Exception $e) {
            http_response_code(response_code: 500);
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
        $data = $this->validateData($params, $this->attendance::GET_FULL_REQUIRED);
        try {
            $result =  $this->attendance->getAttendance(
                columns: ['id', 'count', 'date', 'register'],
                conditions: [
                    'class' => $data['class'],
                    'course' => $data['course'],
                    'date' => $data['date']
                ]
            );

            $this->response = ['success' => true, 'attendance_records' => $result];
        } catch (Exception $e) {
            http_response_code(response_code: 500);
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
        $data = $this->validateData($params, $this->attendance::GET_SUMMARY_REQUIRED);
        try {
            $result = $this->attendance->getAttendance(
                columns: ['id', 'count', 'date'],
                conditions: [
                    'class' => $data['class'],
                    'course' => $data['course'],
                    'year' => $data['year'],
                    'term' => $data['term']
                ]
            );

            $this->response = ['success' => true, 'attendance_records' => $result];
        } catch (Exception $e) {
            http_response_code(response_code: 500);
            $this->response['message'] = $e->getMessage();
        }
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAttendanceById(array $params)
    {
        $data = $this->validateData($params, ['id']);
        try {
            $result = $this->attendance->getAttendance(
                columns: ['id', 'count', 'date', 'register'],
                conditions: ['id' => $data['id']]
            );

            $this->response = ['success' => true, 'attendance_records' => $result];
        } catch (Exception $e) {
            http_response_code(response_code: 500);
            $this->response['message'] = $e->getMessage();
        }
        ResponseHandler::sendJsonResponse($this->response);
    }
}
