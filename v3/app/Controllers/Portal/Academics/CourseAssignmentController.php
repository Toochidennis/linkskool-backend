<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\CourseAssignmentService;

class CourseAssignmentController extends BaseController
{
    private CourseAssignmentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CourseAssignmentService($this->pdo);
    }

    public function storeCourseAssignment()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'year' => 'required|integer',
                'term' => 'required|string|in:1,2,3',
                'staff_id' => 'required|integer|min:1',
                'courses' => 'required|array|filled',
                'courses.*.course_id' => 'required|integer|filled',
                'courses.*.class_id' => 'required|integer|filled'
            ]
        );

        try {
            $isInserted = $this->service->assignCourses($data);

            if ($isInserted) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Course(s) assigned successfully.'
                ], HttpStatus::CREATED);
            }
            return $this->respondError(
                'Failed to assign course(s)',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateCourseAssignment()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'year' => 'required|integer',
                'term' => 'required|string|in:1,2,3',
                'staff_id' => 'required|integer|min:1',
                'courses' => 'required|array|filled',
                'courses.*.course_id' => 'required|integer|filled',
                'courses.*.class_id' => 'required|integer|filled'
            ]
        );

        try {
            $isUpdated = $this->service->updateCourseAssignments($data);

            if ($isUpdated) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Course assignments updated successfully.'
                ]);
            }
            return $this->respondError(
                'Failed to update course assignments',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getCourseAssignments(array $params)
    {
        $data = $this->validateData($params, ['year', 'term', 'staff_id']);

        try {
            $result = $this->service->getAssignedCourses($data);
            $this->respond(['success' => true, 'response' => $result]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
