<?php

namespace V3\App\Controllers\Portal\Academics;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\AttendanceService;

class AttendanceController extends BaseController
{
    private AttendanceService $attendanceService;

    public function __construct()
    {
        parent::__construct();
        $this->attendanceService = new AttendanceService($this->pdo);
    }

    public function addClassAttendance(array $vars)
    {
        $data = $this->validate(
            data: [...$this->post, 'class_id' => $vars['id']],
            rules: [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'staff_id' => 'required|integer',
                'attendance_count' => 'required|integer',
                'class_id' => 'required|integer',
                'students' => 'required|array|min:1',
                'students.*.id' => 'required|integer',
                'students.*.name' => 'required|string|filled',
                'attendance_date' => 'required|string|filled'
            ]
        );

        try {
            [$success, $message, $id] = $this->attendanceService->addAttendance(data: $data);

            if ($success) {
                return $this->respond(
                    [
                        'success' => $success,
                        'message' => $message,
                        'id' => $id
                    ],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError($message, HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function addCourseAttendance(array $vars)
    {
        $data = $this->validate(
            data: [...$this->post, 'course_id' => $vars['id']],
            rules: [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'staff_id' => 'required|integer',
                'attendance_count' => 'required|integer',
                'class_id' => 'required|integer',
                'course_id' => 'required|integer',
                'students' => 'required|array|min:1',
                'students.*.id' => 'required|integer',
                'students.*.name' => 'required|string|filled',
                'attendance_date' => 'required|string|filled'
            ]
        );

        try {
            [$success, $message, $id] = $this->attendanceService->addAttendance(data: $data, isCourse: true);

            if ($success) {
                return $this->respond(
                    [
                        'success' => $success,
                        'message' => $message,
                        'id' => $id
                    ],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError($message, HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateAttendance(array $vars)
    {
        $requiredFields = ['staff_id', 'count', 'register'];
        $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);
        $id = (int) $vars['id'];

        try {
            $id = $this->attendanceService->updateAttendance(data: $data, id: $id);

            if ($id > 0) {
                return $this->respond(
                    [
                        'success' => true,
                        'message' => 'Attendance updated successfully.',
                        'id' => $id
                    ]
                );
            }

            return $this->respondError('Failed to update attendance', HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
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
    public function getClassAttendance(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id', 'attendance_date']);

        try {
            $result = $this->attendanceService
                ->getAttendance(
                    filters: [
                        'class' => $data['id'],
                        'date' => $data['attendance_date']
                    ],
                    singleRecord: true
                );
            return $this->respond(
                [
                    'success' => true,
                    'attendance_records' => $result
                ]
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getCourseAttendance(array $vars)
    {
        $data = $this->validateData(
            $vars,
            ['id', 'class_id', 'date']
        );

        try {
            $results = $this->attendanceService
                ->getAttendance(
                    [
                        'class' => $data['class_id'],
                        'course' => $data['id'],
                        'date' => $data['date']
                    ],
                    singleRecord: true
                );

            return $this->respond(['success' => true, 'attendance_records' => $results]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Returns summary of  attendance data.
     *
     *  * Expected query parameters:
     * - class_id
     * - term
     * - year
     *
     * @param  array $params
     * @return void
     */
    public function getAttendanceHistory(array $vars)
    {
        $data = $this->validateData(
            $vars,
            ['class_id', 'term', 'year']
        );

        try {
            $results = $this->attendanceService->getAttendanceHistory($data);

            return $this->respond(['success' => true, 'attendance_records' => $results]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getAttendanceById(array $params)
    {
        $data = $this->validateData($params, ['id']);
        try {
            $results = $this->attendanceService->getAttendance($data, singleRecord: true);
            return $this->respond(['success' => true, 'attendance_records' => $results]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
