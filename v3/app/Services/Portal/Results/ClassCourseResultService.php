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

use V3\App\Models\Portal\Assessment;
use V3\App\Models\Portal\CourseRegistration;
use V3\App\Models\Portal\Grade;
use V3\App\Models\Portal\Results\ResultCommentModel;
use V3\App\Models\Portal\Student;

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
    public function getAssessments(int $levelId)
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
     * @param array $filters An associative array containing:
     *  - class_id: string
     *  - course_id: string
     *  - term: string
     *  - year: string
     *
     * @return array An array of student result records including student info and raw result fields.
     */
    public function getCourseResults(array $filters)
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

    public function getGrades()
    {
        return $this->grade
            ->select(columns: ['grade_symbol', 'start'])
            ->orderBy('start')
            ->get();
    }

    public function getStudentsResult(array $filters)
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

    public function getComments(array $filters)
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

    public function fetchCompositeResult(array $filters)
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

    public function getClassRegisteredCourses(array $filters)
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
        $raw = $this->getClassRegisteredCourses($filters);

        return array_map(fn($course) => [
            'course_id' => $course['course_id'],
            'course_name' => $course['course_name'],
            'abbr' => $this->abbreviate($course['course_name']),
        ], $raw);
    }

    private function abbreviate($name)
    {
        // Predefined known abbreviations
        $map = [
            'english language' => 'ENG',
            'mathematics' => 'MATH',
            'further mathematics' => 'FMATH',
            'biology' => 'BIO',
            'chemistry' => 'CHEM',
            'physics' => 'PHY',
            'agricultural science' => 'AGR',
            'agric science' => 'AGR',
            'agric' => 'AGR',
            'economics' => 'ECO',
            'civic education' => 'CIV',
            'christian religious studies' => 'CRS',
            'christian religion' => 'CRS',
            'crs' => 'CRS',
            'government' => 'GOVT',
            'literature in english' => 'LIT',
            'literature' => 'LIT',
            'commerce' => 'COM',
            'basic science' => 'BSC',
            'basic technology' => 'BTECH',
            'business studies' => 'BST',
            'computer science' => 'CSC',
            'ict' => 'ICT',
            'physical education' => 'PE',
            'home economics' => 'HEC',
            'history' => 'HIST',
            'french' => 'FR',
            'yoruba' => 'YOR',
            'hausa' => 'HAU',
            'igbo' => 'IGB',
            'music' => 'MUS',
            'visual arts' => 'ART',
            'creative arts' => 'ART',
            'social studies' => 'SOS',
            'code' => 'CODE',
            'catering craft' => 'CCP',
        ];

        $key = strtolower(trim($name));
        if (isset($map[$key])) {
            return $map[$key];
        }

        // Fallback: take first 3 or 4 uppercase letters
        $cleaned = preg_replace('/[^A-Za-z]/', '', $name);
        return strtoupper(substr($cleaned, 0, 4));
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
    public function transformCourseResults($studentResults, $assessments)
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

    public function transformStudentsResult($studentResults, $assessments, $comments)
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

    public function transformCompositeResult($results, $registeredCourses)
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

    public function rankStudents(array $students)
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
}
