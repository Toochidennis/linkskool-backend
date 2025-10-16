<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Exam;
use V3\App\Models\Explore\ExamType;

class CbtService
{
    private Exam $exam;
    private ExamType $examType;

    public function __construct(\PDO $pdo)
    {
        $this->exam = new Exam($pdo);
        $this->examType = new ExamType($pdo);
    }

    /**
     * Fetch all exam data grouped by exam_type → course → year
     */
    public function getFormattedExamHierarchy(): array
    {
        $examRows = $this->getExamRows();
        $examMeta = $this->getExamMeta();

        return $this->formatExamHierarchy($examRows, $examMeta);
    }

    /**
     * Fetch base exam rows (raw data)
     */
    private function getExamRows(): array
    {
        return $this->exam
            ->select([
                'id',
                'course_name',
                'course_id',
                'exam_type',
                'year'
            ])
            ->where('course_name', '<>', '')
            ->groupBy(['exam_type', 'course_id', 'year'])
            ->orderBy([
                'exam_type' => 'ASC',
                'course_id' => 'ASC',
                'year' => 'ASC'
            ])
            ->get();
    }

    /**
     * Fetch metadata for exam types (title, desc, etc.)
     */
    private function getExamMeta(): array
    {
        $rows = $this->examType
            ->select([
                'id',
                'title',
                'description',
                'shortname',
                'picref'
            ])
            ->get();

        $meta = [];
        foreach ($rows as $row) {
            $meta[$row['id']] = [
                'title'       => $row['title'],
                'desc'        => $row['description'],
                'short'       => $row['shortname'],
                'pic'         => $this->formatRef($row['picref'] ?? '')
            ];
        }

        return $meta;
    }

    /**
     * Structure the data in a clear hierarchical format:
     * exam_type → courses → years
     */
    private function formatExamHierarchy(array $rows, array $examMeta): array
    {
        $formatted = [];

        foreach ($rows as $row) {
            $examTypeId = $row['exam_type'];
            $courseId   = $row['course_id'];
            $examId     = $row['id'];

            // Create exam type group
            if (!isset($formatted[$examTypeId])) {
                $meta = $examMeta[$examTypeId] ?? [];
                $formatted[$examTypeId] = [
                    'exam_type_id'   => $examTypeId,
                    'exam_image'     => $meta['pic'] ?? '',
                    'exam_title'     => $meta['title'] ?? '',
                    'exam_desc'      => $meta['desc'] ?? '',
                    'exam_shortname' => $meta['short'] ?? '',
                    'courses'        => []
                ];
            }

            // Add course
            if (!isset($formatted[$examTypeId]['courses'][$courseId])) {
                $formatted[$examTypeId]['courses'][$courseId] = [
                    'course_id'   => $courseId,
                    'course_name' => $row['course_name'],
                    'years'       => []
                ];
            }

            // Add year
            $formatted[$examTypeId]['courses'][$courseId]['years'][] = [
                'exam_id' => $examId,
                'year'    => $row['year']
            ];
        }

        // Convert associative arrays to sequential arrays for JSON
        return array_values(array_map(function ($exam) {
            $exam['courses'] = array_values($exam['courses']);
            return $exam;
        }, $formatted));
    }

    /**
     * Format absolute URL for image reference
     */
    private function formatRef(string $url): string
    {
        return "http://" . $_SERVER["SERVER_NAME"] . "/$url";
    }
}
