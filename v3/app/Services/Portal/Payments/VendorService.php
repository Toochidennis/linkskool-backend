<?php

namespace V3\App\Services\Portal\Payments;

use V3\App\Models\Portal\Payments\Vendor;

class VendorService
{
    private Vendor $vendor;

    public function __construct(\PDO $pdo)
    {
        $this->vendor = new Vendor($pdo);
    }

    public function addVendor(array $data): bool|int
    {
        $payload = [
            'customername' => $data['vendor_name'],
            'customerid' => $data['reference'],
            'telephone' => $data['phone_number'],
            'email' => $data['email'] ?? '',
        ];

        if ($this->isDuplicate($payload)) {
            return false;
        }

        return $this->vendor->insert($payload);
    }

    public function updateVendor(array $data): bool
    {
        $payload = [
            'customername' => $data['vendor_name'],
            'customerid' => $data['reference'],
            'telephone' => $data['phone_number'],
            'email' => $data['email'] ?? '',
        ];

        if ($this->isDuplicate($data)) {
            return false;
        }

        return $this->vendor
            ->where('id', '=', $data['id'])
            ->update($payload);
    }

    private function isDuplicate(array $conditions): bool
    {
        return $this->vendor
            ->where('customername', '=', $conditions['vendor_name'])
            ->where('customerid', '=', $conditions['reference'])
            ->where('telephone', '=', $conditions['phone_number'])
            ->where('email', '=', $conditions['email'])
            ->exists();
    }

    public function getVendors(): array
    {
        return $this->vendor
            ->select(columns: [
                'id',
                'customername AS vendor_name',
                'customerid AS reference',
                'telephone AS phone_number',
                'email',
            ])->get();
    }

    public function deleteVendor(int $id): bool|int
    {
        return $this->vendor
            ->where(column: 'tid', operator: '=', value: $id)
            ->delete();
    }
}
