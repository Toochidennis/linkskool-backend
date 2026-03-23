<?php

namespace V3\App\Services\Portal\Results;

use V3\App\Models\Portal\Results\Grade;
use V3\App\Models\Portal\Results\Result;

class ResultService
{
    private Result $result;
    private Grade $grade;

    /**
     * ResultService constructor.
     *
     * Initializes the Result and Grade models using the given PDO connection.
     *
     * @param \PDO $pdo The PDO instance for database connection.
     */
    public function __construct(\PDO $pdo)
    {
        $this->result = new Result($pdo);
        $this->grade = new Grade($pdo);
    }

    /**
     * Updates result records with computed remarks, comments, and grades.
     *
     * @param array $results An array of result records to update.
     *
     * @return bool Returns true if all the records were updated successfully, false otherwise.
     */
    public function updateRecord(array $results)
    {
        $count = 0;

        foreach ($results as $res) {
            $assessments = json_encode($res['assessments']);
            $legacyScores = implode(':', array_map(
                static fn($assessment) => $assessment['score'] ?? '',
                $res['assessments']
            ));
            $totalScore = $res['total_score'];
            $comment = $this->getComment($totalScore);
            $remark = $this->getRemark($totalScore);

            $updated = $this->result
                ->where('id', '=', $res['result_id'])
                ->update(data: [
                    'result' => $legacyScores,
                    'total' => $totalScore,
                    'grade' => $remark['grade'],
                    'remark' => $remark['remark'],
                    'comment' => $comment,
                    'passed' => $remark['status'],
                    'modified_by' => $res['staff_id'],
                    'date_modified' => date('Y-m-d H:i:s'),
                    'new_result' => $assessments
                ]);

            if ($updated) {
                $count++;
            }
        }

        return $count === \count($results);
    }

    /**
     * Retrieves all grade definitions from the database ordered by descending start scores.
     *
     * @return array An array of grades with keys: grade_symbol, start, and remark.
     */
    private function getGrades()
    {
        return $this->grade
            ->select(columns: ['grade_symbol', 'start', 'remark'])
            ->orderBy(columns: 'start', direction: 'DESC')
            ->get();
    }

    /**
     * Determines a performance comment based on the score.
     *
     * @param float|int|null $score The total score.
     * @return string|null Returns a performance comment or null if score is not provided.
     */
    private function getComment(?int $score): ?string
    {
        if (!is_numeric($score)) {
            return '';
        }

        if ($score >= 80) {
            return 'Excellent';
        } elseif ($score >= 60) {
            return 'Very Good';
        } elseif ($score >= 50) {
            return 'Good';
        } elseif ($score >= 40) {
            return 'Poor';
        } else {
            return 'Very Poor';
        }
    }

    /**
     * Determines grade symbol, remark, and pass status based on score.
     *
     * @param float|int $score The total score.
     * @return array Returns an array with keys:
     */
    private function getRemark(int $score)
    {
        if (!is_numeric($score)) {
            return [
                'grade' => '',
                'remark' => '',
                'status' =>  0
            ];
        }

        $grades = $this->getGrades();

        foreach ($grades as $grade) {
            if ($score >= $grade['start']) {
                $isFail = strtoupper($grade['grade_symbol']) === 'F';
                return [
                    'remark' => $grade['remark'],
                    'grade' => $grade['grade_symbol'],
                    'status' => $isFail ? 0 : 1
                ];
            }
        }

        return [
            'grade' => 'F',
            'remark' => 'Fail',
            'status' =>  0
        ];
    }
}
