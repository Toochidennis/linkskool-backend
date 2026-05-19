<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Classroom\ClassroomAccessCode;

class ClassroomAccessCodeService
{
    private ClassroomAccessCode $model;
    private string $csvPath;

    public function __construct(\PDO $pdo)
    {
        $this->model = new ClassroomAccessCode($pdo);
        $this->csvPath = dirname(__DIR__, 4) . '/config/classroom_access_codes.csv';
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

        return ['status' => 'success', 'id' => $row['id']];
    }

    public function assignCode(string $code, int $institutionId): array|bool
    {
        $codeId = $this->validateCode($code)['id'] ?? null;

        if (!$codeId) {
            return ['message' => 'Invalid or already used access code.'];
        }

        return $this->model
            ->where('id', $codeId)
            ->update([
                'institution_id' => $institutionId,
                'used_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
