<?php

namespace V3\App\Services\Portal;

use InvalidArgumentException;
use V3\App\Models\Portal\CourseRegistration;
use V3\App\Utilities\ResponseHandler;
use V3\App\Utilities\Sanitizer;

class CourseRegistrationService
{
    private CourseRegistration $courseRegistration;

    public function __construct(\PDO $pdo)
    {
        $this->courseRegistration = new CourseRegistration($pdo);
    }

    /**
     * Validates the provided data data and returns sanitized data along with the type.
     *
     * Expects 'year', 'term', 'type', 'courses', and 'class_id' to be provided.
     *
     * @param array $data The data to validate.
     * @throws InvalidArgumentException if required fields are missing.
     */
    public function validateAndGetData(array $data, string $destination = "")
    {
        $requiredFields = match ($destination) {
            'terms' => [
                'year' => 'year is required',
                'class_id' => 'class_id is required',
            ],
            'duplicate' =>  [
                'year' => 'year is required',
                'term' => 'term is required',
                'class_id' => 'class_id is required',
            ],
            default => [
                'type' => 'Type of registration is required',
                'courses' => 'courses is required',
                'year' => 'year is required',
                'term' => 'term is required',
                'class_id' => 'class_id is required',
            ]
        };

        $errors = [];
        foreach ($requiredFields as $field => $errorMessage) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = $errorMessage;
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        return Sanitizer::sanitizeInput($data);
    }

    /**
     * Registers courses for each student.
     *
     * @param array $students  Array of student IDs.
     * @param array $courses   Array of course IDs.
     * @param string $term     The academic term.
     * @param string $year     The academic year.
     * @param mixed $classId   The class identifier.
     *
     * @return bool Returns true if registration is successful for all students.
     */
    public function register($students, $courses, $term, $year, $classId)
    {
        $index = 0;

        foreach ($students as $student) {
            foreach ($courses as $course) {
                $studentId = is_array($student) ? $student['student_id'] : $student;
                $courseId = is_array($course) ? $course['course_id'] : $course;

                $count = $this->courseRegistration->countCourseRegistrations(
                    conditions: [
                        'year' => $year,
                        'term' => $term,
                        'reg_no' => $studentId,
                        'class' => $classId,
                        'course' => $courseId
                    ]
                );

                if ($count === 0) {
                    $this->courseRegistration->registerCourse(data: [
                        'year' => $year,
                        'term' => $term,
                        'reg_no' => $studentId,
                        'class' => $classId,
                        'course' => $courseId
                    ]);
                }
            }
            $index++;
        }

        // After registration, synchronize any outdated courses.
        $this->deleteRegisteredCourses(
            students: $students,
            newCourses: $courses,
            term: $term,
            year: $year,
            classId: $classId
        );

        return $index === count($students) ? true : false;
    }

    /**
     * Synchronizes course registrations by removing any courses that are
     * currently registered for a student but are not in the new list.
     *
     * For each student, this method:
     *   1. Retrieves the current list of registered course IDs.
     *   2. Determines which course IDs in that list are not present in the new registration.
     *   3. Deletes those outdated registrations.
     *
     * @param array  $students   Array of student records (each containing at least an 'id' key).
     * @param array  $newCourses Array of new course IDs that should be registered.
     * @param string $term       The academic term.
     * @param string $year       The academic year.
     * @param mixed  $classId    The class identifier.
     *
     * @return bool True if all deletions were successful; false otherwise.
     */
    private function deleteRegisteredCourses($students, $newCourses, $term, $year, $classId)
    {
        $allSuccess = true;

        foreach ($students as $student) {
            // Extract the student ID; if the student record is just an ID, use it directly.
            $studentId = is_array($student) ? $student['student_id'] : $student;

            $newCoursesFlat = array_map(
                fn($course) => $course['course_id'],
                $newCourses
            );

            $courseRegistration = $this->courseRegistration->deleteRegisteredCourses(
                conditions: [
                    'reg_no' => $studentId,
                    'class'  => $classId,
                    'term'   => $term,
                    'year'   => $year
                ],
                notInColumn: 'course',
                notInValues: $newCoursesFlat
            );

            if (!$courseRegistration) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }
}
