<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\FeeType;

class FeeTypeService
{
    private FeeType $feeType;

    public function __construct(\PDO $pdo)
    {
        $this->feeType = new FeeType($pdo);
    }

    public function addFeeName(array $data): bool|int
    {
        $payload = [
            'fee_name' => $data['fee_name'],
            'mandatory' => $data['is_mandatory']
        ];

        if ($this->isDuplicate($payload['fee_name'])) {
            return false;
        } else {
            return $this->feeType->insert($payload);
        }
    }

    public function updateFeeName(array $data): bool|int
    {
        $payload  = [
            'fee_name' => $data['fee_name'],
            'mandatory' => $data['is_mandatory']
        ];

        if ($this->isDuplicate($payload['fee_name'])) {
            return false;
        } else {
            return $this->feeType->where('tid', '=', $data['id'])->update($payload);
        }
    }

    private function isDuplicate(string $feeName): bool
    {
        return $this->feeType
            ->where('fee_name', '=', $feeName)
            ->exists();
    }

    public function getFeeNames(): array
    {
        return $this->feeType
            ->select(['tid AS id', 'description AS fee_name', 'IFNULL(type, 0) AS is_mandatory'])
            ->get();
    }
}
