<?php

namespace V3\App\Services\Portal;

use PDO;
use Exception;
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
     * @throws Exception
     */
    public function generatePassword(string $surname): string
    {
        return substr($surname, 0, 4) . rand(10000, 90000);
    }
}
