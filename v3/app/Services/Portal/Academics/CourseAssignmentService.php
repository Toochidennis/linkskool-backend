<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Models\Portal\CourseAssignment;

class CourseAssignmentService
{
    private CourseAssignment $courseAssignment;

    public function __construct(PDO $pdo)
    {
        $this->courseAssignment = new CourseAssignment($pdo);
    }

    public function assignCourses(array $data)
    {
        $count = 0;
        foreach ($data['courses'] as $course) {
            $payload = [
                'ref_no' => $data['staff_id'],
                'course' => $course['course_id'],
                'class' => $course['class_id'],
                'year' => $data['year'],
                'term' => $data['term']
            ];

            $inserted = $this->courseAssignment->insert($payload);

            if ($inserted) {
                $count++;
            }
        }

        return $count == count($data['courses']);
    }

    public function getAssignedCourses(array $filters)
    {
        return $this->courseAssignment
            ->select(
                columns: [
                    'staff_course_table.*',
                    'course_table.id',
                    'course_table.course_name',
                    'course_table.course_code',
                    'class_table.id',
                    'class_table.class_name',
                    'class_table.level',
                    'level_table.level_name'
                ]
            )
            ->join('course_table', 'staff_course_table.course = course_table.id')
            ->join('class_table', 'staff_course_table.class = class_table.id')
            ->join('level_table', 'class_table.level = level_table.id')
            ->where('staff_course_table.ref_no', '=', $filters['staff_id'])
            ->where('staff_course_table.year', '=', $filters['year'])
            ->where('staff_course_table.term', '=', $filters['term'])
            ->get();
    }
}
