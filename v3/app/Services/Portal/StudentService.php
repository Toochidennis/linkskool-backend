<?php

namespace V3\App\Services\Portal;

use PDO;
use Exception;
use V3\App\Models\Portal\Student;
use V3\App\Models\Portal\SchoolSettings;
use V3\App\Models\Portal\RegistrationTracker;

class StudentService
{
    private Student $student;
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;

    /**
     * StudentRegistrationService constructor.
     *
     * @param Student         $student
     * @param SchoolSettings  $schoolSettings
     * @param RegistrationTracker $regTracker
     */
    public function __construct(PDO $pdo)
    {
        $this->student = new Student($pdo);
        $this->schoolSettings = new SchoolSettings($pdo);
        $this->regTracker = new RegistrationTracker($pdo);
    }

    /**
     * Generate and update the registration number for a given student.
     *
     * @param int $studentId
     * @return bool
     */
    public function generateRegistrationNumber(int $studentId): bool
    {
        $prefixResult = $this->schoolSettings->select(['student_prefix'])->first();
        $regResult = $this->regTracker->select(['id, student_reg_number'])->first();

        $newNumber = '001';

        if ($prefixResult) {
            $studentPrefix = $prefixResult['student_prefix'];

            if ($regResult) {
                $lastNumber = (int)$regResult['student_reg_number'];
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            $studentRegNumber = "$studentPrefix$studentId$newNumber";
        } else {
            $studentRegNumber = "$studentId$newNumber";
        }

        // Update the student's registration number
        $updateStudentStmt = $this->student
            ->where('id', '=', $studentId)
            ->update(data: ['registration_no' => $studentRegNumber]);

        // Update (or insert) the last used registration number in the tracker
        $regStmt = $regResult ?
            $this->regTracker
            ->where('id', '=', $newNumber)
            ->update(['student_reg_number' => $newNumber])
            :
            $this->regTracker
            ->insert(['student_reg_number' => $newNumber]);

        return $updateStudentStmt && $regStmt;
    }

    /**
     * Generates a hashed password using the student's surname as a seed.
     *
     * @param string $surname
     * @return string
     * @throws Exception
     */
    public function generatePassword(string $surname): string
    {
        return substr($surname, 0, 4) . rand(10000, 90000);
    }
}
