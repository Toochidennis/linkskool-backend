<?php

/**
 *This class helps handle grade insertion
 *
 * PHP 8.2+
 *
 * @category Service
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://linkskool.net
 */

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Models\Portal\Results\Grade;

class GradeService
{
    private Grade $grade;

    public function __construct(PDO $pdo)
    {
        $this->grade = new Grade($pdo);
    }

    public function add(array $grades)
    {
        $grades = array_map(
            fn($grade) =>
            [
                'grade_symbol' => $grade['symbol'],
                'start' => $grade['range'],
                'remark' => $grade['remark']
            ],
            $grades
        );

        $count = 0;

        foreach ($grades as $grade) {
            $insert = $this->grade->insert($grade);

            if ($insert) {
                $count++;
            }
        }

        return $count === count($grades);
    }

    public function update($grade)
    {
        $formatted = [
            'grade_symbol' => $grade['symbol'],
            'start' => $grade['range'],
            'remark' => $grade['remark']
        ];

        return $this->grade
            ->where('id', '=', $grade['id'])
            ->update($formatted);
    }

    public function getGrades()
    {
        return $this->grade->get();
    }

    public function delete($id)
    {
        return $this->grade
            ->where('id', '=', $id)
            ->delete();
    }
}
