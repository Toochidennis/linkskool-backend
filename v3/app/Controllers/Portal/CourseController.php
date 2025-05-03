<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Models\Portal\Course;
use V3\App\Utilities\HttpStatus;
use V3\App\Traits\ValidationTrait;
use V3\App\Controllers\BaseController;

/**
 * Class CourseController
 *
 * Handles course-related operations.
 */
class CourseController extends BaseController
{
    use ValidationTrait;

    private Course $course;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->course = new Course(pdo: $this->pdo);
    }

    public function addCourse()
    {
        $requiredFields = ['course_name', 'course_code'];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $courseId = $this->course->insert($data);

            if ($courseId) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Course added successfully.'
                ], HttpStatus::CREATED);
            }

            return $this->respondError('Failed to create course');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateCourse()
    {
    }

    public function getCourses()
    {
        try {
            $result = $this->course->get();
            $this->respond(['success' => true, 'courses' => $result]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getStudentRegisteredCourses(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['id', 'class_id', 'term', 'year']
        );

        try {
            $result = $this->course
                ->select(columns: ['course_table.id', 'course_table.course_name'])
                ->join('result_table', 'course_table.id = result_table.course')
                ->where('result_table.term', '=', $data['term'])
                ->where('result_table.year', '=', $data['year'])
                ->where('result_table.class', '=', $data['class_id'])
                ->where('result_table.reg_no', '=', $data['id'])
                ->get();

            $this->respond(['success' => true, 'registered_courses' => $result]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getClassRegisteredCourses(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['id', 'term', 'year']
        );

        try {
            $registeredCourses = $this->course
                ->select(
                    columns: [
                        'course_table.id',
                        'course_table.course_name',
                        'AVG(result_table.total) AS average_score'
                    ]
                )
                ->join('result_table', 'course_table.id = result_table.course')
                ->where('result_table.term', '=', $data['term'])
                ->where('result_table.year', '=', $data['year'])
                ->where('result_table.class', '=', $data['id'])
                ->groupBy(['course_table.id', 'course_table.course_name'])
                ->get();

            $registeredCourses = array_map(
                function ($row) {
                    $row['average_score'] = round($row['average_score'] ?? 0, 2);
                    return $row;
                },
                $registeredCourses
            );

            $this->respond(['success' => true, 'registered_courses' => $registeredCourses]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getCourseById()
    {
    }

    public function deleteCourse()
    {
    }
}
