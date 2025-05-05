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

namespace V3\App\Services\Portal;

use V3\App\Models\Portal\Result;

class StudentResultService
{
    private Result $result;

    private function __construct(\PDO $pdo)
    {
        $this->result = new Result($pdo);
    }

    /**
     * Retrieves average scores grouped by academic year and term for a specific student.
     *
     * @param array $filters An associative array with:
     *                       - 'id' => student registration number.
     *
     * @return array Returns a structured array grouped by year with each term’s average score.
     *               Format: [
     *                   '2024' => [
     *                       'terms' => [
     *                           ['term' => 1, 'average_score' => '63.00'],
     *                           ['term' => 2, 'average_score' => '71.50'],
     *                       ]
     *                   ],
     *                   ...
     *               ]
     */
    public function getResultTerms(array $filters)
    {
        $terms = $this->result
            ->select(
                columns: [
                    'reg_no',
                    'class class_id',
                    'year',
                    'term',
                    "avg(result_table.total) AS average_score"
                ]
            )
            ->where('reg_no', $filters['id'])
            ->where('total', 'IS  NOT', null)
            ->groupBy(['class', 'year', 'term'])
            ->orderBy(['year' => 'DESC', 'term' => 'ASC'])
            ->get();

        $structured = [];

        foreach ($terms as $row) {
            $year = $row['year'];

            if (!isset($structured[$year])) {
                $structured[$year] = ['terms' => []];
            }

            $structured[$year]['terms'][] = [
                'term' => (int) $row['term'],
                'average_score' => number_format((float)$row['average_score'], 2)
            ];
        }

        return $structured;
    }

    public function getStudentResult(array $filters)
    {
        // TODO: wait
    }
}
