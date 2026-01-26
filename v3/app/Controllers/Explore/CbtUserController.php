<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\AuthBootstrapService;
use V3\App\Services\Explore\CbtUserService;

#[Group('/public/cbt/users')]
class CbtUserController extends ExploreBaseController
{
    private CbtUserService $userService;
    private AuthBootstrapService $authBootstrapService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new CbtUserService($this->pdo);
        $this->authBootstrapService = new AuthBootstrapService($this->pdo);
    }

    #[Route('', 'POST', ['api'])]
    public function storeUser()
    {
        $data = $this->validate($this->getRequestData(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string',
            'email' => 'required|email|max:255',
            'profile_picture' => 'nullable|string|max:255',
            'attempt' => 'required|integer|min:0',
        ]);

        $user = $this->authBootstrapService->bootstrap($data);

        if (empty($user)) {
            $this->respondError(
                'Failed to create user.',
                HttpStatus::BAD_REQUEST
            );
        }
        $this->respond([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ]);
    }

    #[Route('/google', 'POST', ['api'])]
    public function bootstrapWithGoogleToken()
    {
        $data = $this->validate($this->getRequestData(), [
            'google_token' => 'required|string',
        ]);

        $response = $this->authBootstrapService->bootstrapWithGoogleToken($data['google_token']);

        if (empty($response)) {
            $this->respondError(
                'Failed to authenticate with Google.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Authenticated with Google',
            'data' => $response
        ]);
    }

    #[Route('/signup', 'POST', ['api'])]
    public function signupWithEmail()
    {
        $data = $this->validate($this->getRequestData(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'profile_picture' => 'nullable|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'birth_date' => 'required|date',
            'phone' => 'required|integer',
        ]);

        $user = $this->authBootstrapService->signupWithEmail($data);

        if (empty($user)) {
            $this->respondError(
                'Failed to create user.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ]);
    }

    #[Route('/login', 'POST', ['api'])]
    public function login()
    {
        $data = $this->validate($this->getRequestData(), [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $user = $this->authBootstrapService->loginWithEmailAndPassword($data['email'], $data['password']);

        if (empty($user)) {
            $this->respondError(
                'Invalid credentials.',
                HttpStatus::UNAUTHORIZED
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Login successful',
            'data' => $user
        ]);


        return $user;
    }

    #[Route('/{id:\d+}/phone', 'PUT', ['api'])]
    public function updatePhone(array $vars)
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer|min:1',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string|max:11',
                'birth_date' => 'required|date',
                'gender' => 'required|string|in:male,female,other',
                'profile_picture' => 'nullable|string|max:255',
                'user_id' => 'required|integer|min:1',
            ]
        );

        $response = $this->authBootstrapService
            ->bootstrap($data, $data['phone'], $data['birth_date']);

        if (empty($response)) {
            $this->respondError(
                'Failed to update phone number',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Phone number updated successfully',
            'data' => $response,
        ]);
    }

    #[Route('/{id:\d+}', 'PUT', ['api'])]
    public function updateUser(array $vars)
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer|min:1',
                'type' => 'nullable|string|in:payment,update',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'attempt' => 'nullable|integer|min:0',
                'reference' => 'nullable|string|max:100',
            ]
        );

        $isUpdated = $this->userService->updateUser($data['id'], $data);

        if (!$isUpdated) {
            $this->respondError(
                'Failed to update user',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'User updated successfully',
            ]
        );
    }

    #[Route('/{id:\d+}/payment-status', 'PUT', ['api'])]
    public function updatePaymentStatus(array $vars)
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer|min:1',
                'name' => 'required|string|max:255',
                'reference' => 'required|string|max:100',
            ]
        );

        $isUpdated = $this->userService->updatePaymentStatus($data);

        if (!$isUpdated) {
            $this->respondError(
                'Failed to update payment status',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Payment status updated successfully',
        ]);
    }

    #[Route('/{email}', 'GET', ['api'])]
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
