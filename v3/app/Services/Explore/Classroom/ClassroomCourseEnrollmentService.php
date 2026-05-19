<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Models\Explore\Classroom\ClassroomCourse;
use V3\App\Models\Explore\Classroom\ClassroomCourseEnrollment;
use V3\App\Models\Explore\Classroom\ClassroomCourseQuiz;

class ClassroomCourseEnrollmentService
{
    protected ClassroomCourseEnrollment $model;
    private ClassroomCourse $courseModel;
    private ClassroomCourseQuiz $quizModel;

    public function __construct(\PDO $pdo)
    {
        $this->model       = new ClassroomCourseEnrollment($pdo);
        $this->courseModel = new ClassroomCourse($pdo);
        $this->quizModel   = new ClassroomCourseQuiz($pdo);
    }
    public function enroll(array $data): array
    {
        $course = $this->courseModel
            ->select(['id', 'name', 'description', 'image_url', 'status', 'pricing_type', 'price'])
            ->where('join_code', $data['join_code'])
            ->where('institution_id', $data['institution_id'])
            ->first();

        if (empty($course)) {
            throw new \InvalidArgumentException('Invalid join code or institution.');
        }

        if ($course['status'] !== 'published') {
            throw new \InvalidArgumentException('This course is not currently available for enrollment.');
        }

        $courseId  = (int) $course['id'];
        $studentId = (int) $data['student_id'];

        $existing = $this->model
            ->where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if (!empty($existing)) {
            throw new \InvalidArgumentException('Student is already enrolled in this course.');
        }

        $this->model->insert([
            'course_id'      => $courseId,
            'student_id'     => $studentId,
            'institution_id' => (int) $data['institution_id'],
        ]);

        return [
            'id'           => $courseId,
            'name'         => $course['name'],
            'description'  => $course['description'],
            'image_url'    => AssetUrl::fromAppUrl($course['image_url'] ?? null),
            'pricing_type' => $course['pricing_type'],
            'price'        => $course['price'],
        ];
    }

    public function getEnrolledCourses(int $studentId, int $institutionId): array
    {
        $enrollments = $this->model
            ->select(['course_id'])
            ->where('student_id', $studentId)
            ->where('institution_id', $institutionId)
            ->get();

        if (empty($enrollments)) {
            return [];
        }

        $courseIds = array_column($enrollments, 'course_id');

        return array_map(function (int $courseId) {
            $course = $this->courseModel
                ->select(['id', 'name', 'description', 'image_url', 'pricing_type', 'price'])
                ->where('id', $courseId)
                ->first();

            $quiz = $this->quizModel
                ->select(['course_id', 'lesson_id', 'topic', 'start_date', 'end_date'])
                ->where('course_id', $courseId)
                ->first();

            return [
                'course' => [
                    'id'           => (int) $course['id'],
                    'name'         => $course['name'],
                    'description'  => $course['description'],
                    'image_url'    => AssetUrl::fromAppUrl($course['image_url'] ?? null),
                    'pricing_type' => $course['pricing_type'],
                    'price'        => $course['price'],
                ],
                'quiz' => $quiz ? [
                    'course_id'  => $quiz['course_id'],
                    'lesson_id'  => $quiz['lesson_id'] ?? null,
                    'name'       => $course['name'],
                    'topic'      => $quiz['topic'],
                    'start_date' => $quiz['start_date'],
                    'end_date'   => $quiz['end_date'],
                ] : null,
            ];
        }, $courseIds);
    }
}
