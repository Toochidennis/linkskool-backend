<?php

namespace V3\App\Services;

use V3\App\Models\Staff;
use V3\App\Models\SchoolSettings;
use V3\App\Models\RegistrationTracker;

class StaffService{
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private Staff $staff;

    /**
     * staffRegistrationService constructor.
     *
     * @param staff         $staff
     * @param SchoolSettings  $schoolSettings
     * @param RegistrationTracker $regTracker
     */
    public function __construct(Staff $staff, SchoolSettings $schoolSettings, RegistrationTracker $regTracker)
    {
        $this->staff = $staff;
        $this->schoolSettings = $schoolSettings;
        $this->regTracker = $regTracker;
    }

    /**
     * Generate and update the registration number for a given staff.
     *
     * @param int $staffId
     * @return bool
     */
    public function generateRegistrationNumber(int $staffId): bool
    {
        $prefixResult = $this->schoolSettings->getStaffPrefix();
        $regResult = $this->regTracker->getStaffLastRegNumber();
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
        $updateStaffStmt = $this->staff->updateStaff(
            data: ['staff_no' => $staffRegNumber],
            conditions: ['id' => $staffId]
        );

        // Update (or insert) the last used registration number in the tracker
        $regStmt = $regResult ?
            $this->regTracker->updateRegNumber(
                data: ['staff_reg_number' => $newNumber],
                conditions: ['id' => $regResult['id']]
            ) : $this->regTracker->insertRegNumber(data: ['staff_reg_number' => $newNumber]);

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
}