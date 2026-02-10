<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Payments\AccountService;

#[Group('/portal/payments')]
class AccountController extends BaseController
{
    private AccountService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AccountService($this->pdo);
    }

    #[Route(
        '/accounts',
        'POST',
        ['auth', 'role:admin']
    )]
    public function store()
    {
        $cleanedData = $this->validate(
            data: $this->post,
            rules: [
                'account_number' => 'required|digits:4',
                'account_name' => 'required|string|filled',
                'account_type' => 'required|integer'
            ]
        );

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
    }

    #[Route(
        '/accounts/{id:\d+}',
        'PUT',
        ['auth', 'role:admin']
    )]
    public function update(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'account_number' => 'required|digits:5',
                'account_name' => 'required|string|filled',
                'account_type' => 'required|integer'
            ]
        );

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
    }

    #[Route(
        '/accounts',
        'GET',
        ['auth', 'role:admin']
    )]
    public function get(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'page' => 'required|integer',
                'limit' => 'required|integer',
            ]
        );

        $this->respond([
            'success' => true,
            'response' => $this->service->getPaginatedAccounts(
                $cleanedData['page'],
                $cleanedData['limit']
            ),
        ]);
    }

    #[Route(
        '/accounts/{id:\d+}',
        'DELETE',
        ['auth', 'role:admin']
    )]
    public function delete(array $vars)
    {
        $cleanedData = $this->validate($vars, ['id' => 'required|integer']);

        $newId = $this->service->deleteAccount($cleanedData['id']);

        if ($newId > 0) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'Account deleted successfully'
                ],
            );
        }

        $this->respondError(
            'Failed to delete account. Maybe it doesn\'t exist',
            HttpStatus::BAD_REQUEST
        );
    }
}
