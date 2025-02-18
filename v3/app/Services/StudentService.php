<?php

namespace V3\App\Services;

use V3\App\Models\SchoolSettings;
use V3\App\Models\RegistrationTracker;
use V3\App\Models\Student;

class StudentService
{
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private Student $student;

    /**
     * StudentRegistrationService constructor.
     *
     * @param Student         $student
     * @param SchoolSettings  $schoolSettings
     * @param RegistrationTracker $regTracker
     */
    public function __construct(Student $student, SchoolSettings $schoolSettings, RegistrationTracker $regTracker)
    {
        $this->student = $student;
        $this->schoolSettings = $schoolSettings;
        $this->regTracker = $regTracker;
    }

    /**
     * Generate and update the registration number for a given student.
     *
     * @param int $studentId
     * @return bool
     */
    public function generateRegistrationNumber(int $studentId): bool
    {
        $prefixResult = $this->schoolSettings->getStudentPrefix();
        $regResult = $this->regTracker->getStudentLastRegNumber();
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
        $updateStudentStmt = $this->student->updateStudent(
            data: ['registration_no' => $studentRegNumber],
            conditions: ['id' => $studentId]
        );

        // Update (or insert) the last used registration number in the tracker
        $regStmt = $regResult ?
            $this->regTracker->updateRegNumber(
                data: ['student_reg_number' => $newNumber],
                conditions: ['id' => $regResult['id']]
            ) : $this->regTracker->insertRegNumber(data: ['student_reg_number' => $newNumber]);

        return $updateStudentStmt && $regStmt;
    }

    /**
     * Generates a hashed password using the student's surname as a seed.
     *
     * @param string $surname
     * @return string
     * @throws \Exception
     */
    public function generatePassword(string $surname): string
    {
        return substr($surname, 0, 4) . rand(10000, 90000);
    }
}
