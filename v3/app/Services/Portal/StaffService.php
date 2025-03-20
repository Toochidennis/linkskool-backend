<?php

namespace V3\App\Services\Portal;

use PDO;
use V3\App\Utilities\Sanitizer;
use V3\App\Models\Portal\Staff;
use V3\App\Models\Portal\SchoolSettings;
use V3\App\Models\Portal\RegistrationTracker;

class StaffService
{
    private Staff $staff;
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;

    /**
     * staffRegistrationService constructor.
     *
     * @param staff         $staff
     * @param SchoolSettings  $schoolSettings
     * @param RegistrationTracker $regTracker
     */
    public function __construct(PDO $pdo)
    {
        $this->staff = new Staff(pdo: $pdo);
        $this->schoolSettings = new SchoolSettings($pdo);
        $this->regTracker = new RegistrationTracker($pdo);
    }

    /**
     * Generate and update the registration number for a given staff.
     *
     * @param int $staffId
     * @return bool
     */
    public function generateRegistrationNumber(int $staffId): bool
    {
        $prefixResult = $this->schoolSettings->select(['staff_prefix'])->first();
        $regResult = $this->regTracker->select(['id', 'staff_reg_number'])->first();

        $newNumber = '001';

        if ($prefixResult) {
            $staffPrefix = $prefixResult['staff_prefix'];

            if ($regResult) {
                $lastNumber = (int)$regResult['staff_reg_number'];
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            $staffRegNumber = "$staffPrefix$staffId$newNumber";
        } else {
            $staffRegNumber = "$staffId$newNumber";
        }

        // Update the staff's registration number
        $updateStaffStmt = $this->staff
            ->where('id', '=', $staffId)
            ->update(['staff_no' => $staffRegNumber]);

        // Update (or insert) the last used registration number in the tracker
        $regStmt = $regResult ?
            $this->regTracker
            ->where('id', '=', $regResult['id'])
            ->update(['staff_reg_number' => $newNumber])
            :
            $this->regTracker->insert(data: ['staff_reg_number' => $newNumber]);

        return $updateStaffStmt && $regStmt;
    }

    /**
     * Generates a hashed password using the staff's surname as a seed.
     *
     * @param string $surname
     * @return string
     * @throws \Exception
     */
    public function generatePassword(string $surname): string
    {
        return substr($surname, 0, 4) . rand(10000, 90000);
    }

    /**
     * Validates POST data and returns sanitized data or false on error.
     *
     * @return array|false
     * @throws \InvalidArgumentException
     */
    public function validateAndGetData(array $post)
    {
        // Define an array for required fields with custom error messages
        $requiredFields = [
            'surname' => 'Surname is required.',
            'first_name' => 'First name is required.',
            'sex' => 'Gender is required.',
        ];

        // Loop through required fields and check if they are empty
        $errors = [];
        foreach ($requiredFields as $field => $errorMessage) {
            if (!isset($post[$field]) || empty($post[$field])) {
                $errors[] = $errorMessage;
            }
        }

        // Sanitize and set each input
        $surname = Sanitizer::sanitizeInput($post['surname']);
        $data =  [
            "surname" => $surname,
            "first_name" => Sanitizer::sanitizeInput($post['first_name']),
            "middle" => Sanitizer::sanitizeInput($post['middle'] ?? ''),
            "sex" => (int) Sanitizer::sanitizeInput($post['sex']),
            //"birthdate" => Sanitizer::sanitizeInput($post['birthdate']),
            // "email" => filter_var(Sanitizer::sanitizeInput($post['email'])),
            // "guardian_name" => Sanitizer::sanitizeInput($post['guardian_name']),
            // "guardian_email" => filter_var(Sanitizer::sanitizeInput($post['guardian_email']), FILTER_VALIDATE_EMAIL), // Validate email format
            // "guardian_address" => Sanitizer::sanitizeInput($post['guardian_address']),
            // "guardian_phone_no" => Sanitizer::sanitizeInput($post['guardian_phone_no']),
            // "state_origin" => Sanitizer::sanitizeInput($post['state_origin']),
            "access_level" => (int) Sanitizer::sanitizeInput($post['access_level']),
            "password" => $this->generatePassword($surname)
        ];

        // Check for invalid email
        // if (!$data["guardian_email"]) {
        //     $errors[] = 'Invalid email address';
        // }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
        }

        return $data;
    }
}
