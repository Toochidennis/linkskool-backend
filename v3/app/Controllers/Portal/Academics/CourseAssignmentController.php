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
        $data = $this->validateData(
            $this->post,
            [
                'year',
                'term',
                'staff_id',
                'courses',
                'courses.*.course_id',
                'courses*.*class_id'
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
