<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\FeeType;
use V3\App\Models\Portal\Payments\NextTermFee;

class FeeTypeService
{
    private FeeType $feeType;
    private NextTermFee $nextTermFee;

    public function __construct(\PDO $pdo)
    {
        $this->feeType = new FeeType($pdo);
        $this->nextTermFee = new NextTermFee($pdo);
    }

    public function addFeeName(array $data): bool|int
    {
        $payload = [
            'description' => $data['fee_name'],
            'mandatory' => $data['is_mandatory']
        ];

        if ($this->isDuplicate($payload['description'])) {
            return false;
        } else {
            return $this->feeType->insert($payload);
        }
    }

    public function updateFeeName(array $data): bool|int
    {
        $payload  = [
            'description' => $data['fee_name'],
            'mandatory' => $data['is_mandatory']
        ];

        if ($this->isDuplicate($payload['description'])) {
            return false;
        } else {
            return $this->feeType->where('tid', '=', $data['id'])->update($payload);
        }
    }

    private function isDuplicate(string $feeName): bool
    {
        return $this->feeType
            ->where('description', '=', $feeName)
            ->exists();
    }

    public function getFeeNames(): array
    {
        return $this->feeType
            ->select(['tid AS id', 'description AS fee_name', 'IFNULL(type, 0) AS is_mandatory'])
            ->get();
    }

    public function deleteFeeName(array $filters): bool|int
    {
        $this->nextTermFee
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('fee', '=', $filters['id'])
            ->delete();

        return $this->feeType
            ->where('tid', '=', $filters['id'])
            ->delete();
    }
}
