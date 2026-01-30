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
            // 1. Identity
            $user = $this->cbtUserService->findOrCreateUserByEmail($googleData);

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
                    'first_name' => $googleData['first_name'],
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
                    'gender' => $googleData['gender'],
                    'user_id' => $user['id'],
                ]);
            }

            $this->pdo->commit();

            $user = $this->cbtUserService->getUserByEmail($user['email']);
            unset($user['password']);

            return [
                'user' => $user,
                'profiles' => $profiles
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function bootstrapWithGoogleToken(string $token): array
    {
        $tokens = $this->exchangeCodeForTokens($token);
        $accessToken = $tokens['access_token'] ?? null;

        if (empty($accessToken)) {
            throw new \RuntimeException('Google access token is missing.');
        }

        $googleProfile = $this->fetchGoogleUserProfile($accessToken);
        [$firstName, $lastName] = $this->resolveNames($googleProfile);

        return $this->bootstrap([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $googleProfile['email'],
            'profile_picture' => $googleProfile['picture'] ?? null,
            'gender' => $googleProfile['gender'] ?? null,
        ]);
    }

    private function resolveNames(array $googleProfile): array
    {
        $firstName = $googleProfile['given_name'] ?? null;
        $lastName = $googleProfile['family_name'] ?? null;

        if ((!$firstName || !$lastName) && !empty($googleProfile['name'])) {
            $nameParts = preg_split('/\\s+/', trim($googleProfile['name']), -1, PREG_SPLIT_NO_EMPTY);

            if (!$firstName && !empty($nameParts)) {
                $firstName = array_shift($nameParts);
            }

            if (!$lastName && !empty($nameParts)) {
                $lastName = array_pop($nameParts);
            }
        }

        $firstName = $firstName ?: 'User';
        $lastName = $lastName ?: 'User';

        return [$firstName, $lastName];
    }

    private function fetchGoogleUserProfile(string $token): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => getEnv('GOOGLE_USER_INFO_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new \RuntimeException("Failed to fetch Google profile: {$error}");
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse Google profile response.');
        }

        if ($httpStatus >= 400 || isset($result['error'])) {
            $message = $result['error_description'] ?? $result['error'] ?? 'unknown error';
            throw new \RuntimeException("Google profile request failed: {$message}");
        }

        if (empty($result['email'])) {
            throw new \RuntimeException('Google profile is missing an email address.');
        }

        return $result;
    }

    private function exchangeCodeForTokens(string $code): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => getEnv('GOOGLE_TOKEN_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'code' => $code,
                'client_id' => getEnv('GOOGLE_CLIENT_ID'),
                'client_secret' => getEnv('GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => getEnv('GOOGLE_REDIRECT_URI'),
                'grant_type' => 'authorization_code',
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $result = json_decode($response, true);

        if ($httpStatus >= 400 || isset($result['error'])) {
            throw new \RuntimeException(
                'Token exchange failed: ' . ($result['error_description'] ?? 'unknown error')
            );
        }

        return $result;
    }

    public function signupWithEmail(array $data): array
    {
        $user = $this->cbtUserService->createUserWithEmailAndPassword($data);

        if (!empty($user)) {
            $profiles = $this->profileService->createProfile([
                'user_id' => $user['id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birth_date' => $data['birth_date'],
                'gender' => $data['gender'],
            ]);

            unset($user['password']);

            return [
                'user' => $user,
                'profiles' => $profiles
            ];
        }

        throw new \RuntimeException('Failed to create user.');
    }

    public function loginWithEmailAndPassword(string $email, string $password): array
    {
        $user = $this->cbtUserService->getUserByEmail($email);

        if (empty($user) || !password_verify($password, $user['password'])) {
            throw new \RuntimeException('Invalid credentials.');
        }

        $profiles = $this->profileService->getProfilesByUserId($user['id']);
        unset($user['password']);

        return [
            'user' => $user,
            'profiles' => $profiles
        ];
    }

    public function getProfilesByUserId(int $userId): array
    {
        return $this->profileService->getProfilesByUserId($userId);
    }

    public function createProfile(array $data): array
    {
        return $this->profileService->createProfile($data);
    }
}
