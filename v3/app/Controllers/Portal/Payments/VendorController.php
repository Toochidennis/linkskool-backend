<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Payments\VendorService;

#[Group('/portal/payments')]
class VendorController extends BaseController
{
    private VendorService $vendorService;

    public function __construct()
    {
        parent::__construct();
        $this->vendorService = new VendorService($this->pdo);
    }

    #[Route(
        '/vendors',
        'POST',
        ['auth', 'role:admin']
    )]
    public function store()
    {
        $cleanedData = $this->validate(
            data: $this->post,
            rules: [
                'vendor_name' => 'required|string|filled',
                'reference' => 'required|string|filled',
                'email' => 'sometimes|string',
                'phone_number' => 'required|digits:11',
                'address' => 'sometimes|string|filled',
            ]
        );

        $newId = $this->vendorService->addVendor($cleanedData);

        if ($newId > 0) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'Vendor added successfully'
                ],
                HttpStatus::CREATED
            );
        }

        $this->respondError(
            'Failed to add vendor. Maybe record already exist',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/vendors/{id:\d+}',
        'PUT',
        ['auth', 'role:admin']
    )]
    public function update(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'vendor_name' => 'required|string|filled',
                'reference' => 'required|string|filled',
                'email' => 'sometimes|string',
                'phone_number' => 'required|digits:11',
                'address' => 'sometimes|string|filled',
            ]
        );

        $newId = $this->vendorService->updateVendor($cleanedData);

        if ($newId > 0) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'Vendor updated successfully'
                ],
            );
        }

        $this->respondError(
            'Failed to update vendor. Maybe record already exist',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/vendors',
        'GET',
        ['auth', 'role:admin']
    )]
    public function get()
    {
        $this->respond([
            'success' => true,
            'response' => $this->vendorService->getVendors(),
        ]);
    }

    #[Route(
        '/vendors/{id:\d+}',
        'DELETE',
        ['auth', 'role:admin']
    )]
    public function delete(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
            ]
        );

        $newId = $this->vendorService->deleteVendor($cleanedData['id']);

        if ($newId > 0) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'Vendor deleted successfully'
                ],
            );
        }

        $this->respondError(
            'Failed to delete vendor. Maybe record doesn\'t exist',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/vendors/{id:\d+}/transactions/{year:\d+}',
        'GET',
        ['auth', 'role:admin']
    )]
    public function transactions(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
                'year' => 'required|integer',
            ]
        );

        $this->respond([
            'success' => true,
            'data' => $this->vendorService->getTransactions($cleanedData),
        ]);
    }

    #[Route(
        '/vendors/{id:\d+}/transactions/annual',
        'GET',
        ['auth', 'role:admin']
    )]
    public function annualHistory(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer',
            ]
        );

        $this->respond([
            'success' => true,
            'data' => $this->vendorService->getAnnualHistory($cleanedData['id']),
        ]);
    }
}
