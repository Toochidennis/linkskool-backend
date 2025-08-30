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
            data: [...$this->post, ...$vars],
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
            $response = $this->attendanceService->addAttendance($data);

            if ($response['success']) {
                return $this->respond(
                    $response,
                    HttpStatus::CREATED
                );
            }

            return $this->respondError($response['message'], HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function addCourseAttendance(array $vars)
    {
        $data = $this->validate(
            data: [...$this->post, ...$vars],
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
            $response = $this->attendanceService->addAttendance($data, isCourse: true);

            if ($response['success']) {
                return $this->respond(
                    $response,
                    HttpStatus::CREATED
                );
            }

            return $this->respondError($response['message'], HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateAttendance(array $vars)
    {
        $data = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'staff_id' => 'required|integer',
                'attendance_count' => 'required|integer',
                'students' => 'required|array|min:1',
                'students.*.id' => 'required|integer',
                'students.*.name' => 'required|string|filled',
            ]
        );

        try {
            $response = $this->attendanceService->updateAttendance($data);

            if ($response['success']) {
                return $this->respond($response);
            }

            return $this->respondError($response['message'], HttpStatus::BAD_REQUEST);
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

        try {
            $response = $this->attendanceService->getAttendance(
                filters: [
                    'class' => $data['class_id'],
                    'date' => $data['attendance_date']
                ],
                singleRecord: true
            );

            if ($response['success']) {
                return $this->respond($response);
            }

            return $this->respondError($response['message'], HttpStatus::NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
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

        try {
            $response = $this->attendanceService->getAttendance(
                filters: [
                    'class' => $data['class_id'],
                    'year' => $data['year'],
                    'term' => $data['term'],
                ],
                columns: [
                    'id',
                    'count AS attendance_count',
                    'date AS attendance_date',
                ]
            );

            if ($response['success']) {
                return $this->respond($response);
            }

            return $this->respondError($response['message'], HttpStatus::NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getSingleCourseAttendance(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'course_id' => 'required|integer',
                'class_id' => 'required|integer',
                'attendance_date' => 'required|string|filled'
            ]
        );

        try {
            $response = $this->attendanceService->getAttendance(
                [
                    'class' => $data['class_id'],
                    'course' => $data['course_id'],
                    'date' => $data['attendance_date']
                ],
                singleRecord: true
            );

            if ($response['success']) {
                return $this->respond($response);
            }

            return $this->respondError($response['message'], HttpStatus::NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
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

        try {
            $response = $this->attendanceService->getAttendance(
                filters: [
                    'class' => $data['class_id'],
                    'course' => $data['course_id'],
                    'year' => $data['year'],
                    'term' => $data['term'],
                ],
                columns: [
                    'id',
                    'count AS attendance_count',
                    'date AS attendance_date',
                ]
            );

            if ($response['success']) {
                return $this->respond($response);
            }

            return $this->respondError($response['message'], HttpStatus::NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
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

            if ($response['success']) {
                return $this->respond($response);
            }

            return $this->respondError($response['message'], HttpStatus::NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getAttendanceDetails(array $vars)
    {
        $data = $this->validate($vars, ['id' => 'required|integer']);
        try {
            $response = $this->attendanceService->getAttendance(
                $data,
                singleRecord: true
            );

            if ($response['success']) {
                return $this->respond($response);
            }

            return $this->respondError($response['message'], HttpStatus::NOT_FOUND);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
