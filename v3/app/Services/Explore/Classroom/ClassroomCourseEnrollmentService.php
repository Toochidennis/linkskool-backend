<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Models\Explore\Classroom\ClassroomCourse;
use V3\App\Models\Explore\Classroom\ClassroomCourseEnrollment;
use V3\App\Models\Explore\Classroom\ClassroomCourseQuizSetting;

class ClassroomCourseEnrollmentService
{
    protected ClassroomCourseEnrollment $model;
    private ClassroomCourse $courseModel;
    private ClassroomCourseQuizSetting $quizSettingModel;

    public function __construct(\PDO $pdo)
    {
        $this->model          = new ClassroomCourseEnrollment($pdo);
        $this->courseModel    = new ClassroomCourse($pdo);
        $this->quizSettingModel = new ClassroomCourseQuizSetting($pdo);
    }

    public function enroll(array $data): bool
    {
        $course = $this->courseModel
            ->select(['id', 'name', 'description', 'image_url', 'status', 'pricing_type', 'price'])
            ->where('join_code', $data['join_code'])
            ->where('institution_id', $data['institution_id'])
            ->first();

        if (empty($course)) {
            throw new \RuntimeException('Invalid join code or institution.');
        }

        if ($course['status'] !== 'published') {
            throw new \RuntimeException('This course is not currently available for enrollment.');
        }

        $courseId  = (int) $course['id'];
        $studentId = (int) $data['student_id'];

        $existing = $this->model
            ->where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if (!empty($existing)) {
            return true;
        }

        return $this->model->insert([
            'course_id' => $courseId,
            'student_id' => $studentId,
            'institution_id' => (int) $data['institution_id'],
        ]);
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

            $quizzes = $this->quizSettingModel
                ->select(['id', 'course_id', 'lesson_id', 'topic', 'duration', 'start_date', 'end_date'])
                ->where('course_id', $courseId)
                ->get();

            return [
                'course' => [
                    'id'           => (int) $course['id'],
                    'name'         => $course['name'],
                    'description'  => $course['description'],
                    'image_url'    => AssetUrl::fromAppUrl($course['image_url'] ?? null),
                    'pricing_type' => $course['pricing_type'],
                    'price'        => $course['price'],
                ],
                'assessments' => array_map(fn($q) => [
                    'id'         => (int) $q['id'],
                    'course_id'  => (int) $q['course_id'],
                    'lesson_id'  => $q['lesson_id'] !== null ? (int) $q['lesson_id'] : null,
                    'topic'      => $q['topic'],
                    'duration'   => $q['duration'] !== null ? (int) $q['duration'] : null,
                    'start_date' => $q['start_date'],
                    'end_date'   => $q['end_date'],
                ], $quizzes),
            ];
        }, $courseIds);
    }
}
