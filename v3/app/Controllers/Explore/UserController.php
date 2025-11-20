<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\UserService;

#[Group('/public/cbt')]
class UserController extends ExploreBaseController
{
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService($this->pdo);
    }

    #[Route('/users', 'POST', ['api'])]
    public function storeUser()
    {
        $data = $this->validate($this->getRequestData(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'profile_picture' => 'sometimes|string|max:255',
            'attempt' => 'required|integer|min:0',
        ]);

        $userId = $this->userService->createUser($data);

        if ($userId <= 0) {
            $this->respondError(
                'Failed to create user, maybe email already exists.',
                HttpStatus::BAD_REQUEST
            );
        }
        $this->respond([
            'success' => true,
            'message' => 'User created successfully',
            'userId' => $userId
        ]);
    }

    #[Route('/users/{id:\d+}', 'PUT', ['api'])]
    public function updateUser(array $vars)
    {
        $data = $this->validate([$this->getRequestData(), ...$vars], [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'attempt' => 'sometimes|integer|min:0',
            'subscribed' => 'sometimes|in:0,1',
            'reference' => 'sometimes|string|max:100',
            'id' => 'required|integer|min:1',
        ]);

        $isUpdated = $this->userService->updateUser($data['id'], $data);

        if (!$isUpdated) {
            $this->respondError(
                'Failed to update user',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'User updated successfully',
        ]);
    }

    #[Route('/users/{email}', 'GET', ['api'])]
    public function getUserByEmail(array $vars)
    {
        $data = $this->validate($vars, [
            'email' => 'required|email|max:255',
        ]);

        $user = $this->userService->getUserByEmail($data['email']);

        if (empty($user)) {
            $this->respondError(
                'User not found',
                HttpStatus::NOT_FOUND
            );
        }

        $this->respond([
            'success' => true,
            'data' => $user
        ]);
    }
}
