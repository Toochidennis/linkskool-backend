<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\FeeType;
use V3\App\Models\Portal\Payments\Invoice;

class FeeTypeService
{
    private FeeType $feeType;
    private Invoice $invoice;

    public function __construct(\PDO $pdo)
    {
        $this->feeType = new FeeType($pdo);
        $this->invoice = new Invoice($pdo);
    }

    public function addFeeName(array $data): bool|int
    {
        $payload = [
            'description' => $data['fee_name'],
            'mandatory' => $data['is_mandatory']
        ];

        if ($this->isDuplicate($payload)) {
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

        if ($this->isDuplicate($payload)) {
            return false;
        } else {
            return $this->feeType->where('tid', '=', $data['id'])->update($payload);
        }
    }

    private function isDuplicate(array $filters): bool
    {
        return $this->feeType
            ->where('description', '=', $filters['description'])
            ->where('mandatory', '=', $filters['mandatory'])
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
        $this->invoice
            ->where('year', '=', $filters['year'])
            ->where('term', '=', $filters['term'])
            ->where('fee', '=', $filters['id'])
            ->delete();

        return $this->feeType
            ->where('tid', '=', $filters['id'])
            ->delete();
    }
}
