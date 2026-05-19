<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\ClassroomAccessCode;

class ClassroomAccessCodeService
{
    private ClassroomAccessCode $model;
    private string $csvPath;

    public function __construct(\PDO $pdo)
    {
        $this->model = new ClassroomAccessCode($pdo);
        $this->csvPath = dirname(__DIR__, 3) . '/config/classroom_access_codes.csv';
    }

    public function seedCodes(): int
    {
        $handle = fopen($this->csvPath, 'r');
        if (!$handle) {
            return 0;
        }

        fgetcsv($handle); // skip header

        $seeded = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $code = trim($row[0]);
            if (!$code) {
                continue;
            }

            $exists = $this->model->where('code', $code)->exists();
            if (!$exists) {
                $this->model->insert(['code' => $code]);
                $seeded++;
            }
        }

        fclose($handle);
        return $seeded;
    }

    public function validateCode(string $code): array
    {
        $row = $this->model->where('code', $code)->first();

        if (!$row) {
            return ['status' => 'error', 'message' => 'Invalid access code.'];
        }

        if (!empty($row['institution_id'])) {
            return ['status' => 'error', 'message' => 'Access code has already been used.'];
        }

        return ['status' => 'success', 'message' => 'Access code applied successfully.'];
    }
}
