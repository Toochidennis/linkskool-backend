<?php

namespace  V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Models\Portal\Academics\SchoolSettings;
use V3\App\Models\Portal\Academics\Staff;

class StaffService
{
    private Staff $staff;
    private SchoolSettings $schoolSettings;

    /**
     * staffRegistrationService constructor.
     *
     * @param staff               $staff
     * @param SchoolSettings      $schoolSettings
     */
    public function __construct(PDO $pdo)
    {
        $this->staff = new Staff(pdo: $pdo);
        $this->schoolSettings = new SchoolSettings($pdo);
    }

    public function insertStaffRecord(array $data): bool
    {
        $payload = [
            
        ];

        $staffId = $this->staff->insert($payload);

        if ($staffId) {
            $prefixResult = $this->schoolSettings->select(['staff_prefix'])->first();

            if ($prefixResult) {
                $staffPrefix = $prefixResult['staff_prefix'];
                $staffRegNumber = "$staffPrefix$staffId";
            } else {
                $staffRegNumber = "000$staffId";
            }

            // Update the staff's registration number
            $updateStaffStmt = $this->staff
                ->where('id', '=', $staffId)
                ->update(['staff_no' => $staffRegNumber]);

            return $updateStaffStmt;
        }
        return false;
    }

    /**
     * Generates a hashed password using the staff's surname as a seed.
     *
     * @param  string $surname
     * @return string
     */
    public function generatePassword(string $surname): string
    {
        return substr($surname, 0, 4) . rand(10000, 90000);
    }

    public function getStaff()
    {
        $results = $this->staff->select(
            columns: [
                'id',
                'picture_ref',
                'surname',
                'first_name',
                'middle',
                'staff_no'
            ]
        )->get();

        return array_map(
            fn($row) => [
                'id' => $row['id'],
                'profile_url' => $row['picture_ref'],
                'surname' => $row['surname'],
                'first_name' => $row['first_name'],
                'middle' => $row['middle'],
                'staff_no' => $row['staff_no'],
            ],
            $results
        );
    }
}
