<?php

/**
 * This class helps handles student's course result
 *
 * PHP version 8.2+
 *
 * @category Service
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Services\Portal\Results;

use V3\App\Common\Utilities\SubjectAbbreviation;
use V3\App\Models\Portal\Academics\Assessment;
use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\Results\CourseRegistration;
use V3\App\Models\Portal\Results\Grade;
use V3\App\Models\Portal\Results\ResultCommentModel;

class ClassCourseResultService
{
    private Assessment $assessment;
    private Student $student;
    private Grade $grade;
    private ResultCommentModel $resultComment;
    private CourseRegistration $courseRegistration;

    /**
     * CourseResultService constructor.
     *
     * @param \PDO $pdo PDO database connection instance.
     */
    public function __construct(\PDO $pdo)
    {
        $this->assessment = new Assessment($pdo);
        $this->student = new Student($pdo);
        $this->grade = new Grade($pdo);
        $this->resultComment = new ResultCommentModel($pdo);
        $this->courseRegistration = new CourseRegistration($pdo);
    }

    /**
     * Retrieves a list of assessments for a given level.
     * If no level-specific assessments exist, it falls back to default (no level).
     *
     * @param string $levelId The level ID to filter assessments by.
     *
     * @return array An array of assessments with their names and maximum scores.
     */
    public function fetchLevelAssessments(int $levelId)
    {
        $assessments = $this->assessment
            ->select(columns: ['assesment_name AS assessment_name', 'max_score'])
            ->where('type', '<>', 1)
            ->where('level', '=', $levelId)
            ->orderBy('id')
            ->get();

        if (empty($assessments)) {
            $assessments = $this->assessment
                ->select(columns: ['assesment_name AS assessment_name', 'max_score'])
                ->where('type', '<>', 1)
                ->where('level', '=', "")
                ->orderBy('id')
                ->get();
        }

        return $assessments;
    }

    /**
     * Fetches course results for students based on provided filters.
     *
     * @param array $filters An associative array
     *
     * @return array An array of student result records including student info and raw result fields.
     */
    private function fetchRawCourseResults(array $filters)
    {
        return $this->student
            ->select(columns: [
                "CONCAT(surname, ' ', first_name, ' ', middle) as student_name",
                'students_record.registration_no AS reg_no',
                'result_table.id AS result_id',
                'result_table.result',
                'result_table.new_result',
                'result_table.total'
            ])
            ->join('result_table', 'students_record.id = result_table.reg_no')
            ->where('result_table.class', '=', $filters['class_id'])
            ->where('result_table.year', '=', $filters['year'])
            ->where('result_table.term', '=', $filters['term'])
            ->where('result_table.course', '=', $filters['course_id'])
            ->orderBy('surname')
            ->get();
    }

    private function fetchGradingScale()
    {
        return $this->grade
            ->select(columns: ['grade_symbol', 'start'])
            ->orderBy('start')
            ->get();
    }

    private function fetchAllStudentResults(array $filters)
    {
        return $this->student
            ->select(columns: [
                'students_record.id',
                "CONCAT(surname, ' ', first_name, ' ', middle) as student_name",
                'students_record.registration_no AS reg_no',
                'course_table.course_name',
                'result_table.result',
                'result_table.new_result',
                'result_table.total',
                'result_table.remark',
                'result_table.grade'
            ])
            ->join('result_table', 'students_record.id = result_table.reg_no')
            ->join('course_table', 'course_table.id = result_table.course')
            ->where('result_table.class', '=', $filters['class_id'])
            ->where('result_table.year', '=', $filters['year'])
            ->where('result_table.term', '=', $filters['term'])
            ->orderBy('surname')
            ->get();
    }

    private function fetchResultComments(array $filters)
    {
        $comments = [];
        $results = $this->resultComment
            ->select(columns: ['reg_no', 'principal', 'form_teacher'])
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->get();

        foreach ($results as $result) {
            $comments[$result['reg_no']] =  [
                'principal_comment' => $result['principal'],
                'teacher_comment' => $result['form_teacher']
            ];
        }

        return $comments;
    }

    private function fetchCompositeResult(array $filters)
    {
        return $this->student
            ->select(columns: [
                "CONCAT(surname, ' ', first_name, ' ', middle) as student_name",
                'students_record.registration_no AS reg_no',
                'course_table.course_name',
                'course_table.id AS course_id',
                'result_table.total'
            ])
            ->join('result_table', 'students_record.id = result_table.reg_no')
            ->join('course_table', 'course_table.id = result_table.course')
            ->where('result_table.class', '=', $filters['class_id'])
            ->where('result_table.year', '=', $filters['year'])
            ->where('result_table.term', '=', $filters['term'])
            ->orderBy('surname')
            ->get();
    }

    private function fetchRegisteredCourses(array $filters)
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

    public function getFormattedRegisteredCourses(array $filters)
    {
        $raw = $this->fetchRegisteredCourses($filters);

        return array_map(fn($course) => [
            'course_id' => $course['course_id'],
            'course_name' => $course['course_name'],
            'abbr' => SubjectAbbreviation::abbreviate($course['course_name']),
        ], $raw);
    }

    /**
     * Transforms raw student result data into structured assessment formats.
     *
     * - Uses legacy colon-delimited format if `new_result` is empty.
     * - Uses decoded JSON if `new_result` is present.
     * - Defaults to blank scores if no result is available.
     *
     * @param array $studentResults Raw result records from the database.
     * @param array $assessments List of assessments with names and max scores.
     *
     * @return array A structured array of student results with assessment details.
     */
    private function formatCourseAssessments($studentResults, $assessments)
    {
        return array_map(function ($row) use ($assessments) {
            $structured = [];

            if (!empty($row['result']) && $row['new_result'] === null) {
                $splitScores = explode(':', $row['result']);
                foreach ($assessments as $index => $assess) {
                    $structured[] = [
                        'assessment_name' => $assess['assessment_name'],
                        'score' => isset($splitScores[$index]) ? (int) $splitScores[$index] : '',
                        'max_score' => $assess['max_score']
                    ];
                }
            } elseif ($row['new_result'] !== null) {
                $structured = json_decode($row['new_result'], true);
            } else {
                foreach ($assessments as $assess) {
                    $structured[] = [
                        'assessment_name' => $assess['assessment_name'],
                        'score' => '',
                        'max_score' => $assess['max_score']
                    ];
                }
            }

            return [
                'student_name' => $row['student_name'],
                'reg_no' => $row['reg_no'],
                'result_id' => $row['result_id'],
                'total_score' => $row['total'] !== null ? (float) $row['total'] : '',
                'assessments' => $structured
            ];
        }, $studentResults);
    }

    private function formatAllStudentResults($studentResults, $assessments, $comments)
    {
        $grouped = [];

        foreach ($studentResults as $row) {
            $studentKey = $row['id'];

            if (!isset($grouped[$studentKey])) {
                $grouped[$studentKey] = [
                    'student_id' => $row['id'],
                    'student_name' => $row['student_name'],
                    'reg_no' => $row['reg_no'],
                    'comments' => $comments[$studentKey] ?? '',
                    'subjects' => []
                ];
            }

            // Build assessments structure
            $structured = [];

            if (!empty($row['result']) && $row['new_result'] === null) {
                $splitScores = explode(':', $row['result']);
                foreach ($assessments as $index => $assess) {
                    $structured[] = [
                        'assessment_name' => $assess['assessment_name'],
                        'score' => isset($splitScores[$index]) ? (int) $splitScores[$index] : ''
                    ];
                }
            } elseif ($row['new_result'] !== null) {
                $structured = json_decode($row['new_result'], true);
            } else {
                continue;
            }

            // Append subject data
            $grouped[$studentKey]['subjects'][] = [
                'course_name' => $row['course_name'],
                'total_score' => $row['total'] !== null ? (float) $row['total'] : '',
                'assessments' => $structured,
                'grade' => $row['grade'],
                'remark' => $row['remark']
            ];
        }

        // Filter out students with no subjects
        $filtered = array_filter($grouped, fn($student) => !empty($student['subjects']));

        // Convert associative array to indexed array
        return array_values($filtered);
    }

    private function formatCompositeScores($results, $registeredCourses)
    {
        $students = [];

        // Build subject ID map
        $subjectsMap = [];
        foreach ($registeredCourses as $subject) {
            $subjectsMap[$subject['course_id']] = [
                'course_id' => $subject['course_id'],
                'course_name' => $subject['course_name'],
                'abbr' => $subject['abbr'],
            ];
        }

        // Group scores per student
        foreach ($results as $row) {
            $regNo = $row['reg_no'];
            $score = $row['total'];

            if (!isset($students[$regNo])) {
                $students[$regNo] = [
                    'reg_no' => $regNo,
                    'student_name' => $row['student_name'],
                    'subjects' => [],
                    'total_score' => 0,
                    'subject_count' => 0,
                    'average' => 0,
                    'position' => 0,
                ];
            }

            if ($score !== null) {
                $students[$regNo]['subjects'][$row['course_id']] = round((float)$score, 2);
                $students[$regNo]['total_score'] += $score;
                $students[$regNo]['subject_count'] += 1;
            } else {
                $students[$regNo]['subjects'][$row['course_id']] = null;
            }
        }

        // Compute average
        foreach ($students as &$student) {
            $student['average'] = $student['subject_count'] > 0
                ? round($student['total_score'] / $student['subject_count'], 2)
                : 0;
        }

        // Step 3: Rank students
        $students = $this->rankStudents(array_values($students));

        return [
            'subjects' => array_values($subjectsMap),
            'students' => $students
        ];
    }

    private function rankStudents(array $students)
    {
        // Sort by average descending
        usort($students, fn($a, $b) => $b['average'] <=> $a['average']);

        $position = 1;
        $lastAverage = null;
        $sameRankCount = 0;

        foreach ($students as $index => &$student) {
            if ($lastAverage === $student['average']) {
                // Tie in average: same position
                $student['position'] = $position;
                $sameRankCount++;
            } else {
                // New average
                $position += $sameRankCount;
                $student['position'] = $position;
                $sameRankCount = 1;
            }

            $lastAverage = $student['average'];
            unset($student['subject_count']);
        }

        return $students;
    }

    public function fetchTermCourseResult(array $filters)
    {
        $assessments = $this->fetchLevelAssessments($filters['level_id']);
        $rawResults = $this->fetchRawCourseResults($filters);

        return [
            'course_results' => $this->formatCourseAssessments($rawResults, $assessments),
            'grades' => $this->fetchGradingScale()
        ];
    }

    public function fetchStudentsTermResultWithComments(array $filters)
    {
        $assessments = $this->fetchLevelAssessments($filters['level_id']);
        $rawResults = $this->fetchAllStudentResults($filters);
        $comments = $this->fetchResultComments($filters);

        return $this->formatAllStudentResults(
            $rawResults,
            $assessments,
            $comments
        );
    }

    public function fetchTermCompositeResult(array $filters)
    {
        $results = $this->fetchCompositeResult($filters);
        $registeredCourses = $this->getFormattedRegisteredCourses($filters);

        return $this->formatCompositeScores($results, $registeredCourses);
    }
}
