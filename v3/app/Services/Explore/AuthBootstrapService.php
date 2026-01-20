<?php

namespace V3\App\Services\Explore;

use PDO;
use Throwable;

class AuthBootstrapService
{
    private CbtUserService $cbtUserService;
    private ProgramProfileService $profileService;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->cbtUserService = new CbtUserService($pdo);
        $this->profileService = new ProgramProfileService($pdo);
    }

    /**
     * Bootstrap identity + profile
     *
     * - Creates user if not exists
     * - Creates default profile if none exists
     * - Updates phone (user) if provided
     * - Updates birth_day (profile) if provided
     */
    public function bootstrap(array $googleData, ?string $phone = null, ?string $birthDate = null): array
    {
        $this->pdo->beginTransaction();

        try {
            $payload = [
                'first_name' => $googleData['first_name'] . ' ' . $googleData['last_name'],
                'email' => $googleData['email'],
                'profile_picture' => $googleData['profile_picture'] ?? null,
                'attempt' => $googleData['attempt'] ?? 1,
            ];

            // 1. Identity
            $user = $this->cbtUserService->findOrCreateUserByEmail($payload);

            // 2. Update phone on identity if missing
            if ($phone && empty($user['phone'])) {
                $this->cbtUserService->updateUser($user['id'], [
                    'phone' => $phone
                ]);
            }

            // 3. Profiles
            $profiles = $this->profileService->getProfilesByUserId($user['id']);

            // 4. Create default profile if none exists
            if (empty($profiles)) {
                $this->profileService->createProfile([
                    'user_id' => $user['id'],
                    'first_name' => $payload['first_name'],
                    'last_name' => $googleData['last_name'],
                    'birth_date' => $birthDate,
                ]);

                $profiles = $this->profileService->getProfilesByUserId($user['id']);
            }

            // 5. Update birth day on default profile if provided
            if ($birthDate && !empty($profiles)) {
                $this->profileService->updateProfile([
                    'id' => $profiles[0]['id'],
                    'birth_date' => $birthDate,
                ]);
            }

            $this->pdo->commit();

            return [
                'user' => $this->cbtUserService->getUserByEmail($user['email']),
                'profiles' => $profiles
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
