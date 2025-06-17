<?php

namespace V3\App\Controllers\Portal\Result;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\AttendanceService;

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
        $requiredFields = [
            'year',
            'term',
            'staff_id',
            'count',
            'class',
            'register',
            'date'
        ];

        $data = $this->validateData($this->post + ['class' => $vars['id']], $requiredFields);

        try {
            $inserted = $this->attendanceService->addAttendance($data, false);

            if ($inserted) {
                return $this->respond(
                    ['success' => true, 'message' => 'Attendance added successfully.'],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to add attendance', HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function addCourseAttendance(array $vars)
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

        $data = $this->validateData(data: $this->post + ['course' => $vars['id']], requiredFields: $requiredFields);

        try {
            $inserted = $this->attendanceService->addAttendance(data: $data, isCourse: true);

            if ($inserted) {
                return $this->respond(
                    ['success' => true, 'message' => 'Attendance added successfully.'],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to add attendance', HttpStatus::BAD_REQUEST);
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
            $updated = $this->attendanceService->updateAttendance(data: $data, id: $id);

            if ($updated) {
                return $this->respond(
                    ['success' => true, 'message' => 'Attendance updated successfully.']
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
        $data = $this->validateData(data: $vars, requiredFields: ['id', 'date']);

        try {
            $result = $this->attendanceService
                ->getAttendance(
                    filters: [
                        'class' => $data['id'],
                        'date' => $data['date']
                    ],
                    singleRecord: true
                );
            return $this->respond(['success' => true, 'attendance_records' => $result]);
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
