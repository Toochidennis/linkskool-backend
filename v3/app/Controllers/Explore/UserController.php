<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\UserService;

#[Group('/public/users')]
class UserController extends ExploreBaseController
{
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService($this->pdo);
    }

    #[Route('', 'POST', ['api', 'auth', 'role:admin'])]
    public function storeUser(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'first_name' => 'required|string|filled|max:255',
            'last_name' => 'required|string|filled|max:255',
            'username' => 'required|string|filled|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|filled|min:6',
            'role_id' => 'nullable|integer|min:0',
            'access_level' => 'required|string|in:student,staff',
            'picture_ref' => 'nullable|string|max:255',
        ]);

        $userId = $this->userService->createUser($data);

        if ($userId) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'User created successfully',
                    'user_id' => $userId,
                ],
                HttpStatus::CREATED
            );
        }

        $this->respondError(
            'Failed to create user',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('/login', 'POST', ['api'])]
    public function login(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'username' => 'required|string|filled|max:255',
            'password' => 'required|string|filled|min:6',
        ]);

        $user = $this->userService->login(
            $data['username'],
            $data['password']
        );

        if (!empty($user)) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'Login successful',
                    ...$user,
                ],
                HttpStatus::OK
            );
        }

        $this->respondError(
            'Invalid username or password',
            HttpStatus::UNAUTHORIZED
        );
    }

    #[Route('', 'PUT', ['api', 'auth', 'role:admin'])]
    public function updateUser(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'id' => 'required|integer|min:1',
            'first_name' => 'required|string|filled|max:255',
            'last_name' => 'required|string|filled|max:255',
            'username' => 'required|string|filled|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|integer|min:0',
            'access_level' => 'required|string|in:student,staff',
            'picture_ref' => 'nullable|string|max:255',
        ]);

        $updated = $this->userService->updateUser($data);

        if ($updated) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'User updated successfully',
                ],
                HttpStatus::OK
            );
        }

        $this->respondError(
            'Failed to update user',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('', 'GET', ['api', 'auth', 'role:admin'])]
    public function getUsers(): void
    {
        $this->respond(
            [
                'success' => true,
                'data' => $this->userService->getUsers(),
            ],
            HttpStatus::OK
        );
    }
}
