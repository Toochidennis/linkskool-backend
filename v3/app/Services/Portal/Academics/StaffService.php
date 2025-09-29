<?php

namespace  V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\Academics\SchoolSettings;
use V3\App\Models\Portal\Academics\Staff;

class StaffService
{
    private Staff $staff;
    private SchoolSettings $schoolSettings;
    private FileHandler $fileHandler;

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
        $this->fileHandler = new FileHandler();
    }

    public function insertStaffRecord(array $data): bool
    {
        if (!empty($data['photo']) && is_array($data['photo'])) {
            $data['photo']['type'] = 'image';
            $file = $this->fileHandler->handleFiles($data['photo']);
            $data['photo'] = $file[0]['old_file_name'];
        }

        $payload = [
            'picture_ref' => $data['photo'] ?? '',
            'surname' => $data['last_name'],
            'first_name' => $data['first_name'],
            'middle' => $data['middle_name'] ?? '',
            'sex' => $data['gender'],
            'birthdate' => $data['birth_date'] ?? '',
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
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
            'date_employed' => $data['employment_date'] ?? '',
            'status' => $data['employment_status'] ?? '',
            'health_appraisal' => $data['health_appraisal'] ?? '',
            'appraisal' => $data['general_appraisal'] ?? '',
            'grade' => $data['grade'] ?? '',
            'department' => $data['department'] ?? '',
            'section' => $data['section'] ?? '',
            'designation' => $data['designation'] ?? '',
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

    public function updateStaffRecord(array $data): bool
    {
        if (!empty($data['photo']) && is_array($data['photo'])) {
            $data['photo']['type'] = 'image';
            $file = $this->fileHandler->handleFiles($data['photo']);
            $data['photo'] = $file[0]['old_file_name'];
        }

        $payload = [
            'picture_ref' => $data['photo'] ?? '',
            'surname' => $data['last_name'],
            'first_name' => $data['first_name'],
            'middle' => $data['middle_name'] ?? '',
            'sex' => $data['gender'],
            'birthdate' => $data['birth_date'] ?? '',
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
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
            'date_employed' => $data['employment_date'] ?? '',
            'status' => $data['employment_status'] ?? '',
            'health_appraisal' => $data['health_appraisal'] ?? '',
            'appraisal' => $data['general_appraisal'] ?? '',
            'grade' => $data['grade'] ?? '',
            'department' => $data['department'] ?? '',
            'section' => $data['section'] ?? '',
            'designation' => $data['designation'] ?? '',
            'access_level' => $data['access_level'],
        ];

        return $this->staff->where('id', '=', $data['id'])
            ->update($payload);
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

    public function getStaff(): array
    {
        return $this->staff
            ->select(
                columns: [
                    'id',
                    'picture_ref AS photo',
                    'surname AS last_name',
                    'first_name',
                    'middle AS middle_name',
                    'sex AS gender',
                    'birthdate AS birth_date',
                    'address',
                    'city',
                    'state',
                    'country',
                    'phone AS phone_number',
                    'email AS email_address',
                    'religion',
                    'marital_status',
                    'local_government_origin AS lga_origin',
                    'state_origin',
                    'nationality',
                    'town AS home_town',
                    'health_status',
                    'past_record',
                    'past_record2 AS past_record_extra',
                    'p_record AS personal_record',
                    'work_record AS employment_history',
                    'referees',
                    'additional AS extra_note',
                    'registrationtime',
                    'kin_name AS next_of_kin_name',
                    'kin_address AS next_of_kin_address',
                    'kin_email AS next_of_kin_email',
                    'kin_phone_no AS next_of_kin_phone',
                    'date_employed AS employment_date',
                    'status AS employment_status',
                    'health_appraisal',
                    'appraisal AS general_appraisal',
                    'grade',
                    'department',
                    'section',
                    'designation',
                    'access_level',
                    'staff_no'
                ]
            )
            ->get();
    }

    public function deleteStaff(int $id): bool
    {
        return $this->staff
            ->where('id', '=', $id)
            ->delete();
    }
}
