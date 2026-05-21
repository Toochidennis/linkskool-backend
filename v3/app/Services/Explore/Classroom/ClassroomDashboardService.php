<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Models\Explore\Classroom\ClassroomCourse;
use V3\App\Models\Explore\Classroom\ClassroomCourseQuizSetting;
use V3\App\Models\Explore\Classroom\ClassroomStaff;
use V3\App\Models\Explore\Classroom\ClassroomStudent;

class ClassroomDashboardService
{
    private ClassroomCourse $courseModel;
    private ClassroomStudent $studentModel;
    private ClassroomStaff $staffModel;
    private ClassroomCourseQuizSetting $quizSettingModel;

    public function __construct(\PDO $pdo)
    {
        $this->courseModel = new ClassroomCourse($pdo);
        $this->studentModel = new ClassroomStudent($pdo);
        $this->staffModel = new ClassroomStaff($pdo);
        $this->quizSettingModel = new ClassroomCourseQuizSetting($pdo);
    }

    public function getDashboard(int $institutionId): array
    {
        $totalCourses = $this->courseModel->where('institution_id', $institutionId)->count();
        $totalStudents = $this->studentModel->where('institution_id', $institutionId)->count();
        $totalStaff = $this->staffModel->where('institution_id', $institutionId)->count();
        $totalAssessments = $this->quizSettingModel->where('institution_id', $institutionId)->count();

        $courses = $this->courseModel
            ->select(['id', 'name', 'description', 'image_url', 'pricing_type', 'price', 'status'])
            ->where('institution_id', $institutionId)
            ->get();

        $courseList = array_map(fn(array $course) => [
            'id' => (int) $course['id'],
            'name' => $course['name'],
            'description'  => $course['description'],
            'image_url' => AssetUrl::fromAppUrl($course['image_url'] ?? null),
            'pricing_type' => $course['pricing_type'],
            'price' => $course['price'],
            'status'  => $course['status'],
        ], $courses);

        return [
            'summary' => [
                'total_courses'     => (int) $totalCourses,
                'total_students'    => (int) $totalStudents,
                'total_staff'       => (int) $totalStaff,
                'total_assessments' => (int) $totalAssessments,
            ],
            'courses' => $courseList,
        ];
    }
}
