<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Common\Utilities\Str;
use V3\App\Common\Utilities\Uuid;
use V3\App\Models\Explore\Classroom\ClassroomCourse;
use V3\App\Models\Explore\Classroom\ClassroomCourseLesson;
use V3\App\Models\Explore\Classroom\ClassroomCourseQuizSetting;
use V3\App\Services\Explore\StorageService;

class ClassroomCourseService
{
    private ClassroomCourse $classroomCourse;
    private ClassroomCourseLesson $lessonModel;
    private ClassroomCourseQuizSetting $quizSettingModel;

    public function __construct(\PDO $pdo)
    {
        $this->classroomCourse  = new ClassroomCourse($pdo);
        $this->lessonModel      = new ClassroomCourseLesson($pdo);
        $this->quizSettingModel = new ClassroomCourseQuizSetting($pdo);
    }

    public function createCourse(array $data): bool
    {
        $courseSlug = Str::slug($data['name']);

        if (isset($_FILES['image'])) {
            $data['image_url'] = StorageService::saveFile(
                $_FILES['image'],
                "explore/classrooms/courses/{$courseSlug}/image"
            );
        }

        $payload = [
            'slug' => Uuid::v4(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'institution_id' => $data['institution_id'],
            'image_url' => $data['image_url'] ?? null,
            'created_by' => $data['created_by'],
            'subject_id' => $data['subject_id'] ?? null,
            'level_id' => $data['level_id'] ?? null,
            'duration' => $data['duration'] ?? null,
            'pricing_type' => $data['pricing_type'] ?? 'free',
            'price' => $data['price'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'status' => $data['status'],
            'join_code' => $data['join_code'],
        ];

        return $this->classroomCourse->insert($payload);
    }

    public function getCoursesByInstitution(
        int $institutionId,
        array $filters = [],
        int $page = 1,
        int $limit = 20
    ): array {
        $query = $this->classroomCourse
            ->select([
                'classroom_courses.*',
                'course_table.course_name AS subject_name',
            ])
            ->join('course_table', 'classroom_courses.subject_id = course_table.id', 'LEFT')
            ->where('classroom_courses.institution_id', $institutionId);

        if (!empty($filters['level_id'])) {
            $query->where('classroom_courses.level_id', $filters['level_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('classroom_courses.status', $filters['status']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('classroom_courses.subject_id', $filters['subject_id']);
        }

        $result = $query->paginate($page, $limit);

        $result['data'] = array_map(function (array $row): array {
            $row['image_url']    = AssetUrl::fromAppUrl($row['image_url'] ?? null);
            $row['subject_name'] = $row['subject_name'] ? ucwords(strtolower($row['subject_name'])) : null;
            return $row;
        }, $result['data']);

        return $result;
    }

    public function getCourseById(int $id): array
    {
        $course = $this->classroomCourse
            ->select([
                'classroom_courses.*',
                'course_table.course_name AS subject_name',
            ])
            ->join('course_table', 'classroom_courses.subject_id = course_table.id', 'LEFT')
            ->where('classroom_courses.id', $id)
            ->first();

        if (empty($course)) {
            return [];
        }

        $course['image_url']    = AssetUrl::fromAppUrl($course['image_url'] ?? null);
        $course['subject_name'] = $course['subject_name'] ? ucwords(strtolower($course['subject_name'])) : null;

        return $course;
    }

    public function updateCourseStatus(int $id, string $status): bool
    {
        return $this->classroomCourse
            ->where('id', $id)
            ->update([
                'status'     => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function updateCourse(int $id, array $data): bool
    {
        if (isset($_FILES['image'])) {
            $courseSlug = Str::slug($data['name'] ?? (string) $id);
            $data['image_url'] = StorageService::saveFile(
                $_FILES['image'],
                "explore/classrooms/courses/{$courseSlug}/image"
            );
        }

        $payload = [
            'name'  => $data['name'],
            'description'    => $data['description'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'level_id' => $data['level_id'] ?? null,
            'duration'  => $data['duration'] ?? null,
            'pricing_type'   => $data['pricing_type'] ?? 'free',
            'price'          => $data['price'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'status'         => $data['status'],
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['image_url'])) {
            $payload['image_url'] = $data['image_url'];

            if (!empty($data['old_image_url'])) {
                StorageService::deleteFile($data['old_image_url']);
            }
        }

        return $this->classroomCourse->where('id', $id)->update($payload);
    }

    public function getCourseContent(int $courseId): array
    {
        $lessonSql = "
            SELECT
                l.id,
                l.title,
                l.display_order,
                l.status,
                l.lesson_date,
                l.is_final_lesson,
                (l.video_url IS NOT NULL OR l.recorded_video_url IS NOT NULL) AS has_video,
                EXISTS(
                    SELECT 1 FROM classroom_course_lesson_assignments a
                    WHERE a.lesson_id = l.id
                ) AS has_assignment,
                EXISTS(
                    SELECT 1 FROM classroom_course_lesson_files f
                    WHERE f.lesson_id = l.id AND f.type = 'material'
                ) AS has_material,
                EXISTS(
                    SELECT 1 FROM classroom_course_lesson_files f
                    WHERE f.lesson_id = l.id AND f.type = 'certificate'
                ) AS has_certificate,
                EXISTS(
                    SELECT 1 FROM classroom_course_quiz_settings q
                    WHERE q.lesson_id = l.id
                ) AS has_quiz
            FROM classroom_course_lessons l
            WHERE l.course_id = :course_id
            ORDER BY l.display_order ASC
        ";

        $quizSql = "
            SELECT id, topic, duration, start_date, end_date
            FROM classroom_course_quiz_settings
            WHERE course_id = :course_id
            AND lesson_id IS NULL
        ";

        $lessons = $this->lessonModel->rawQuery($lessonSql, ['course_id' => $courseId]);
        $quizzes = $this->quizSettingModel->rawQuery($quizSql, ['course_id' => $courseId]);

        $items = [];

        foreach ($lessons as $row) {
            $items[] = [
                'type'            => 'lesson',
                'id'              => (int) $row['id'],
                'title'           => $row['title'],
                'display_order'   => (int) $row['display_order'],
                'status'          => $row['status'],
                'lesson_date'     => $row['lesson_date'],
                'is_final_lesson' => (bool) $row['is_final_lesson'],
                'has_video'       => (bool) $row['has_video'],
                'has_assignment'  => (bool) $row['has_assignment'],
                'has_material'    => (bool) $row['has_material'],
                'has_certificate' => (bool) $row['has_certificate'],
                'has_quiz'        => (bool) $row['has_quiz'],
            ];
        }

        foreach ($quizzes as $row) {
            $items[] = [
                'type'       => 'quiz',
                'id'         => (int) $row['id'],
                'topic'      => $row['topic'],
                'duration'   => $row['duration'] !== null ? (int) $row['duration'] : null,
                'start_date' => $row['start_date'],
                'end_date'   => $row['end_date'],
            ];
        }

        return $items;
    }
}
