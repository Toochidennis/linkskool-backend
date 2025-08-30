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
            'picture_ref' => $data['photo'] ?? null,
            'surname' => $data['last_name'],
            'first_name' => $data['first_name'],
            'middle' => $data['middle_name'] ?? '',
            'sex' => $data['gender'],
            'birthdate' => $data['birth_date'] ?? null,
            'address' => $data['home_address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state_id'] ?? null,
            'country' => $data['country'] ?? '',
            'phone' => $data['phone_number'] ?? '',
            'email' => $data['email_address'] ?? '',
            'religion' => $data['religion'] ?? '',
            'marital_status' => $data['marital_status'] ?? '',
            'local_government_origin' => $data['lga_origin'] ?? '',
            'state_origin' => $data['state_origin'] ?? '',
            'nationality' => $data['nationality'] ?? '',
            'town' => $data['home_town'] ?? '',
            'health_status' => $data['health_status'] ?? '',
            'past_record' => $data['past_record'] ?? '',
            'past_record2' => $data['past_record_extra'] ?? '',
            'p_record' => $data['personal_record'] ?? '',
            'work_record' => $data['employment_history'] ?? '',
            'referees' => $data['referees'] ?? '',
            'additional' => $data['extra_note'] ?? '',
            'registrationtime' => date('Y-m-d H:i:s'),
            'kin_name' => $data['next_of_kin_name'] ?? '',
            'kin_address' => $data['next_of_kin_address'] ?? '',
            'kin_email' => $data['next_of_kin_email'] ?? '',
            'kin_phone_no' => $data['next_of_kin_phone'] ?? '',
            'date_employed' => $data['employment_date'] ?? null,
            'status' => $data['employment_status'] ?? '',
            'health_appraisal' => $data['health_appraisal'] ?? '',
            'appraisal' => $data['general_appraisal'] ?? '',
            'grade' => $data['grade_id'] ?? null,
            'department' => $data['department_id'] ?? null,
            'section' => $data['section_id'] ?? null,
            'designation' => $data['designation_id'] ?? null,
            'access_level' => $data['access_level'],
            'password' => $this->generatePassword($data['surname'])
        ];

        $staffId = $this->staff->insert($payload);

        if ($staffId) {
            $prefixResult = $this->schoolSettings
                ->select(['staff_prefix'])
                ->first();

            if (!empty($prefixResult)) {
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
