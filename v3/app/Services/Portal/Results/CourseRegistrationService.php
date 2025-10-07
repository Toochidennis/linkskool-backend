<?php

namespace V3\App\Services\Portal\Results;

use PDO;
use Exception;
use V3\App\Common\Utilities\SubjectAbbreviation;
use V3\App\Models\Portal\Academics\SchoolSettings;
use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\Results\CourseRegistration;

class CourseRegistrationService
{
    private CourseRegistration $courseRegistration;
    private Student $student;
    private SchoolSettings $settings;

    public function __construct(PDO $pdo)
    {
        $this->courseRegistration = new CourseRegistration($pdo);
        $this->student = new Student($pdo);
        $this->settings = new SchoolSettings($pdo);
    }



    /**
     * Duplicates the most recent course registrations of a class to the next academic term.
     *
     * This method finds the latest course registration for a specific class based on the
     * highest year and term, then duplicates those registrations for the following term.
     *
     * If the last term is the 3rd term, it rolls over to the 1st term of the next academic year.
     * Both students and courses from the last registration are duplicated.
     *
     * ### Example Flow:
     * - If the last registration was in **Term 2, 2024**, this will copy the registrations to **Term 3, 2024**.
     * - If the last registration was in **Term 3, 2024**, this will copy the registrations to **Term 1, 2025**.
     *
     * @param int $classId  The ID of the class whose course registrations are to be duplicated.
     *
     * @return bool Returns true if the duplication and registration were successful.
     *
     * @throws Exception If no existing registration is found for the class.
     * @throws Exception If there are no course registrations to duplicate.
     *
     * @uses self::registerCourses() for performing the actual course registration.
     */
    public function duplicateRegistrationForNextTerm(int $classId): bool
    {
        $lastReg = $this->courseRegistration
            ->select(columns: ['year', 'term'])
            ->where('class', '=', $classId)
            ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
            ->first();

        if (empty($lastReg)) {
            return false;
        }

        $oldYear = (int) $lastReg['year'];
        $oldTerm = (int) $lastReg['term'];

        $currentSettings = $this->getSettings();
        $currentYear = (int) $currentSettings['year'];
        $currentTerm = (int) $currentSettings['term'];

        // Calculate the new year and term
        $newTerm = $oldTerm < 3 ? $oldTerm + 1 : 1;
        $newYear = $oldTerm < 3 ? $oldYear : $oldYear + 1;

        // Guard 1: Do not duplicate if old year/term is already same as current settings
        if ($oldYear === $currentYear && $oldTerm === $currentTerm) {
            return false;
        }

        //  Guard 2: Do not duplicate beyond current term/year
        // (e.g. system is 2025 term 3 but computed new is 2026 term 1 → not allowed)
        if (
            $newYear > $currentYear ||
            ($newYear === $currentYear && $newTerm > $currentTerm)
        ) {
            return false;
        }

        //  Guard 3: Only duplicate forward if current session > last registration
        // (oldTerm/oldYear are before currentTerm/currentYear)
        if (
            $oldYear > $currentYear ||
            ($oldYear === $currentYear && $oldTerm > $currentTerm)
        ) {
            return false;
        }

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

        return $this->registerCourses(
            [
                'students' => $students,
                'registered_courses' => $courses,
                'term' => $newTerm,
                'year' => $newYear,
                'class_id' => $classId
            ]
        );
    }

    private function getSettings(): array
    {
        return $this->settings
            ->select(['year', 'term'])
            ->first();
    }

    /**
     * Registers a list of courses for all students in a given class for a specific term and year.
     *
     * @throws Exception If no students are found in the specified class.
     *
     * @uses self::registerCourses() Called internally to perform course registrations.
     */
    public function registerClassCourses(array $data): bool
    {
        $students = $this->student
            ->select(['id AS student_id'])
            ->where('student_class', '=', $data['class_id'])
            ->get();

        if (empty($students)) {
            throw new Exception("There are no students in this class.");
        }

        return $this->registerCourses(['students' => $students, ...$data]);
    }

    /**
     * Registers courses for each student.
     *
     * @param array  $students Array data
     *
     * @return bool Returns true if registration is successful for all students.
     */
    public function registerCourses(array $data)
    {
        $students = $data['students'] ?? ['student_id' => $data['student_id']];
        $classId = $data['class_id'];
        $term = $data['term'];
        $year = $data['year'];
        $courses =  $data['registered_courses'];

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
        $this->unregisterStudentsFromCourses(
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
     * @return bool True if all deletions were successful; false otherwise.
     */
    private function unregisterStudentsFromCourses($students, $newCourses, $term, $year, $classId): bool
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

    /**
     * Retrieves and formats the list of academic terms for which course registrations
     * have been recorded for a specific class.
     *
     * @param int $classId The ID of the class whose registration terms are to be retrieved.
     *
     * @return array Returns an array of years, each containing the list of associated terms.
     *               The structure is grouped and sorted in descending order by year.
     *
     * @throws Exception Never explicitly thrown by this method but may bubble up
     *                    from underlying database calls if errors occur.
     */
    public function getClassRegistrationTerms(int $classId): array
    {
        $formatted = [];
        // Fetch existing registrations for the class.
        $registrations = $this->courseRegistration
            ->select(columns: ['year', 'term', 'AVG(total) AS average_score'])
            ->where('class', '=', $classId)
            ->groupBy(['term', 'year'])
            ->orderBy(['year' => 'DESC', 'term' => 'ASC'])
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

            $formatted[$year]['year'] = (int)$year;
            $formatted[$year]['terms'][] = [
                'term_name' => $termName,
                'term_value' => (int)$termValue,
                'average_score' => round((float)$registration['average_score'], 2)
            ];
        }

        return array_values($formatted);
    }

    /**
     * Get all registration terms (grouped by year), including total number of students in the class.
     */
    public function getClassRegistrationHistory(int $classId): array
    {
        return [
            'total_students' =>  $this->student
                ->where('student_class', '=', $classId)
                ->count(),
            'sessions' => $this->getClassRegistrationTerms($classId)
        ];
    }

    /**
     * Get all registered courses in a class for a given term and year,
     */
    public function getClassRegisteredCourses($filters)
    {
        return $this->courseRegistration
            ->select([
                'course_table.id AS course_id',
                'course_table.course_name'
            ])
            ->join('course_table', 'course_table.id = result_table.course')
            ->where('result_table.class', '=', $filters['class_id'])
            ->where('result_table.term', '=', $filters['term'])
            ->where('result_table.year', '=', $filters['year'])
            ->groupBy(['course_table.id', 'course_table.course_name'])
            ->orderBy('course_table.course_name')
            ->get();
    }

    public function getCoursesRegisteredByStudent(array $filters)
    {
        return $this->courseRegistration
            ->select(columns: ['course_table.id', 'course_table.course_name'])
            ->join(
                table: 'course_table',
                condition: function ($join) use ($filters) {
                    $join->on('course_table.id', '=', 'result_table.course')
                        ->on('result_table.term', '=', $filters['term'])
                        ->on('result_table.year', '=', $filters['year'])
                        ->on('result_table.class', '=', $filters['class_id']);
                }
            )
            ->where('result_table.reg_no', '=', $filters['student_id'])
            ->get();
    }

    /**
     * Get all registered courses in a class for a given term and year,
     * including average scores (for charting).
     */
    public function getClassRegisteredCoursesWithAverageScores(array $filters)
    {
        $registeredCourses = $this->courseRegistration
            ->select(
                columns: [
                    'course_table.id AS course_id',
                    'course_table.course_name',
                    'ROUND(AVG(result_table.total), 2) AS average_score'
                ]
            )
            ->join('course_table', 'course_table.id = result_table.course')
            ->where('result_table.term', '=', $filters['term'])
            ->where('result_table.year', '=', $filters['year'])
            ->where('result_table.class', '=', $filters['class_id'])
            ->groupBy(['course_table.id', 'course_table.course_name'])
            ->orderBy('course_table.course_name')
            ->get();

        return array_map(
            function ($row) {
                $row['average_score'] ??= 0;
                $row['abbr'] = SubjectAbbreviation::abbreviate($row['course_name']);
                return $row;
            },
            $registeredCourses
        );
    }

    /**
     * Return only students who registered for a specific course.
     */
    public function getStudentsForCourseInClass($filters)
    {
        return $this->student
            ->select([
                'students_record.id',
                "CONCAT(surname,' ', first_name,' ', middle) AS student_name"
            ])
            ->join('result_table', 'students_record.id = result_table.reg_no')
            ->where('result_table.class', '=', $filters['class_id'])
            ->where('result_table.course', '=', $filters['course_id'])
            ->where('result_table.term', '=', $filters['term'])
            ->where('result_table.year', '=', $filters['year'])
            ->groupBy(['students_record.id', 'student_name'])
            ->get();
    }

    public function getStudentsRegistrationStatus(array $filters)
    {
        return $this->student
            ->select(columns: [
                'students_record.id',
                "concat(surname,' ', first_name,' ', middle) AS student_name",
                "COUNT(result_table.course) AS course_count",
            ])
            ->join(
                table: 'result_table',
                condition: function ($join) use ($filters) {
                    $join->on('students_record.id', '=', 'result_table.reg_no')
                        ->on('result_table.term', '=', $filters['term'])
                        ->on('result_table.year', '=', $filters['year']);
                },
                type: 'LEFT'
            )
            ->where('students_record.student_class', '=', $filters['class_id'])
            ->groupBy(['students_record.id', 'student_name'])
            ->get();
    }
}
