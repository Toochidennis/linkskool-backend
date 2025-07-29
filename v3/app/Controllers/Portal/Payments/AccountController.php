<?php

namespace V3\App\Controllers\Portal\Payments;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Payments\AccountService;

class AccountController extends BaseController
{
    private AccountService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AccountService($this->pdo);
    }

    public function store()
    {
        $cleanedData = $this->validate(
            data: $this->post,
            rules: [
                'account_id' => 'required|digits:4',
                'account_name' => 'required|string|filled',
                'account_type' => 'required|integer'
            ]
        );

        try {
            $newId = $this->service->addAccount($cleanedData);

            if ($newId > 0) {
                $this->respond(
                    [
                        'success' => true,
                        'message' => 'Account added successfully'
                    ],
                    HttpStatus::CREATED
                );
            }

            $this->respondError(
                'Failed to add account. Maybe it already exist',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'account_id' => 'required|digits:4',
                'account_name' => 'required|string|filled',
                'account_type' => 'required|integer'
            ]
        );

        try {
            $newId = $this->service->updateAccount($cleanedData);

            if ($newId > 0) {
                $this->respond(
                    [
                        'success' => true,
                        'message' => 'Account updated successfully'
                    ],
                );
            }

            $this->respondError(
                'Failed to update account. Maybe it already exist',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function get(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'page' => 'required|integer',
                'limit' => 'required|integer',
            ]
        );

        try {
            $this->respond([
                'success' => true,
                'response' => $this->service->getPaginatedAccounts(
                    $cleanedData['page'],
                    $cleanedData['limit']
                ),
            ]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
