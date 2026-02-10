<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CbtUser;

class CbtUserService
{
    private CbtUser $user;

    public function __construct(\PDO $pdo)
    {
        $this->user = new CbtUser($pdo);
    }

    public function createUser(array $data): int
    {
        return $this->user->insert($data);
    }

    public function findOrCreateUserByEmail(array $data): array
    {
        $payload = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'profile_picture' => $data['profile_picture'] ?? null,
            'attempt' => $data['attempt'] ?? 0,
            'phone' => $data['phone'] ?? null,
        ];

        $user = $this->user
            ->where('email', '=', $payload['email'])
            ->first();

        if ($user) {
            $this->updateUser($user['id'], [
                'first_name' => $payload['first_name'] ?? $user['first_name'],
                'last_name' => $payload['last_name'] ?? $user['last_name'],
            ]);

            return $this->user
                ->where('id', '=', $user['id'])
                ->first();
        }

        try {
            $id = $this->createUser($payload);

            return $this->user
                ->where('id', '=', $id)
                ->first();
        } catch (\PDOException $e) {
            if ($this->isDuplicateEmailError($e)) {
                return $this->getUserByEmail($data['email']);
            }

            throw $e;
        }
    }

    public function createUserWithEmailAndPassword(array $data): array
    {
        $existing = $this->user
            ->where('email', '=', $data['email'])
            ->exists();

        if ($existing) {
            throw new \RuntimeException('Email already in use.');
        }

        $id = $this->user->insert([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'phone' => $data['phone'],
        ]);

        return $this->user
            ->where('id', '=', $id)
            ->first();
    }

    private function isDuplicateEmailError(\PDOException $e): bool
    {
        return $e->getCode() === '23000';
    }

    public function updateUser(int $id, array $data): bool
    {
        unset($data['id']);
        return $this->user
            ->where('id', '=', $id)
            ->update($data);
    }

    public function getUserByEmail(string $email): array
    {
        return $this->user
            ->where('email', '=', $email)
            ->first();
    }

    public function deleteUser(int $id): bool
    {
        return $this->user
            ->where('id', '=', $id)
            ->delete();
    }
}
