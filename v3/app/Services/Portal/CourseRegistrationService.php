<?php

namespace V3\App\Services\Portal;

use PDO;
use Exception;
use V3\App\Models\Portal\Student;
use V3\App\Models\Portal\CourseRegistration;

class CourseRegistrationService
{
    private CourseRegistration $courseRegistration;
    private Student $student;

    public function __construct(PDO $pdo)
    {
        $this->courseRegistration = new CourseRegistration($pdo);
        $this->student = new Student($pdo);
    }

    public function duplicateRegistrationForNextTerm($classId)
    {
        $lastReg = $this->courseRegistration
            ->select(columns: ['year', 'term'])
            ->where('class', '=', $classId)
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->first();

        if (empty($lastReg)) {
            throw new Exception('No existing registration found.');
        }

        $oldYear = $lastReg['year'];
        $oldTerm = $lastReg['term'];

        // Calculate the new year and term
        $newTerm = $oldTerm < 3 ? $oldTerm + 1 : 1;
        $newYear = $oldTerm < 3 ? $oldYear : $oldYear + 1;

        // Fetch existing registrations for the class.
        $oldRegistrations = $this->courseRegistration
            ->select(columns: ['course AS course_id', 'reg_no AS student_id'])
            ->where('class', '=', $classId)
            ->where('year', '=', $oldYear)
            ->where('term', '=', $oldTerm)
            ->get();

        if (empty($oldRegistrations)) {
            throw new Exception('No registrations found to duplicate.');
        }

        // Extract unique students and courses
        $students = array_map(
            fn($id) => ['student_id' => $id],
            array_unique(array_column($oldRegistrations, 'student_id'))
        );
        $courses  = array_map(
            fn($id) => ['course_id' => $id],
            array_unique(array_column($oldRegistrations, 'course_id'))
        );

        return $this->register(
            students: $students,
            courses: $courses,
            term: $newTerm,
            year: $newYear,
            classId: $classId
        );
    }

    public function registerClassCourses(array $data)
    {
        $students = $this->student
            ->select(['id AS student_id'])
            ->where('student_class', '=', $data['class_id'])
            ->get();

        if (empty($students)) {
            throw new Exception("There are no students in this class.");
        }

        return $this->register(
            students: $students,
            courses: $data['registered_courses'],
            term: $data['term'],
            year: $data['year'],
            classId: $data['class_id']
        );
    }

    /**
     * Registers courses for each student.
     *
     * @param array  $students Array of student IDs.
     * @param array  $courses  Array of course IDs.
     * @param string $term     The academic term.
     * @param string $year     The academic year.
     * @param mixed  $classId  The class identifier.
     *
     * @return bool Returns true if registration is successful for all students.
     */
    public function register($students, $courses, $term, $year, $classId)
    {
        $students = !is_array($students) ? ['student_id' => $students] : $students;
        $index = 0;

        foreach ($students as $student) {
            foreach ($courses as $course) {
                $studentId = is_array($student) ? $student['student_id'] : $student;
                $courseId = is_array($course) ? $course['course_id'] : $course;

                $exists = $this->courseRegistration
                    ->where('year', '=', $year)
                    ->where('term', '=', $term)
                    ->where('reg_no', '=', $studentId)
                    ->where('class', '=', $classId)
                    ->where('course', '=', $courseId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $this->courseRegistration->insert(
                    data: [
                        'year' => $year,
                        'term' => $term,
                        'reg_no' => $studentId,
                        'class' => $classId,
                        'course' => $courseId
                    ]
                );
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

        return $index === count($students);
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
                fn($course) =>
                is_array($course) ? $course['course_id'] : $course,
                $newCourses
            );

            $courseRegistration = $this->courseRegistration
                ->where('year', '=', $year)
                ->where('term', '=', $term)
                ->where('reg_no', '=', $studentId)
                ->where('class', '=', $classId)
                ->notIn('course', $newCoursesFlat)
                ->delete();

            if (!$courseRegistration) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    public function getRegistrationTerms($classId)
    {
        $formatted = [];
        // Fetch existing registrations for the class.
        $registrations = $this->courseRegistration
            ->select(columns: ['year', 'term'])
            ->where('class', '=', $classId)
            ->groupBy(['term', 'year'])
            ->orderBy('year', 'DESC')
            ->get();

        foreach ($registrations as $registration) {
            $year = $registration['year'];
            $termValue = $registration['term'];

            if ($year === '0000') {
                continue;
            }

            $termName = match ($termValue) {
                1 => 'First Term',
                2 => 'Second Term',
                3 => 'Third Term',
                default => 'Unknown Term'
            };

            // Group by year
            if (!isset($formatted[$year])) {
                $formatted[$year] = [
                    'year' => (int)$year,
                    'terms' => []
                ];
            }

            $formatted[$year]['terms'][] = [
                'term_name' => $termName,
                'term_value' => $termValue
            ];
        }

        return array_values($formatted);
    }
}
