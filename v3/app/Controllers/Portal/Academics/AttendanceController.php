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

    public function addAttendance(array $vars)
    {
        $data = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'type' => 'required|string|in:course,class',
                'year' => 'required|integer',
                'term' => 'required|integer',
                'staff_id' => 'required|integer',
                'attendance_count' => 'required|integer',
                'class_id' => 'required|integer',
                'course_id' => 'required_if:type,course|integer',
                'students' => 'required|array|min:1',
                'students.*.id' => 'required|integer',
                'students.*.name' => 'required|string|filled',
                'attendance_date' => 'required|date'
            ]
        );

        try {
            $response = $this->attendanceService->upsertAttendance($data);
            return $response['success']
                ? $this->respond($response, HttpStatus::CREATED)
                : $this->respondError($response['message'], HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getSingleClassAttendance(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'attendance_date' => 'required|string|filled'
            ]
        );

        return $this->handleAttendanceFetch([
            'class' => $data['class_id'],
            'date'  => $data['attendance_date']
        ], true);
    }

    public function getAllClassAttendance(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer'
            ]
        );

        return $this->handleAttendanceFetch([
            'class' => $data['class_id'],
            'year'  => $data['year'],
            'term'  => $data['term']
        ]);
    }

    public function getSingleCourseAttendance(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'course_id' => 'required|integer',
                'class_id' => 'required|integer',
                'attendance_date' => 'required|date'
            ]
        );

        return $this->handleAttendanceFetch([
            'class'  => $data['class_id'],
            'course' => $data['course_id'],
            'date'   => $data['attendance_date']
        ], true);
    }

    public function getAllCourseAttendance(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer',
                'course_id' => 'required|integer'
            ]
        );

        return $this->handleAttendanceFetch([
            'class'  => $data['class_id'],
            'course' => $data['course_id'],
            'year'   => $data['year'],
            'term'   => $data['term']
        ]);
    }

    public function getAttendanceHistory(array $vars)
    {
        $data = $this->validate(
            $vars,
            [
                'class_id' => 'required|integer',
                'term' => 'required|integer',
                'year' => 'required|integer'
            ]
        );

        try {
            $response = $this->attendanceService->getAttendanceHistory($data);
            return $this->respond($response);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getAttendanceDetails(array $vars)
    {
        $data = $this->validate($vars, ['id' => 'required|integer']);
        return $this->handleAttendanceFetch(['id' => $data['id']], true);
    }

    private function handleAttendanceFetch(array $filters, bool $single = false)
    {
        try {
            return $this->respond(
                $this->attendanceService
                    ->getAttendance($filters, singleRecord: $single)
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
