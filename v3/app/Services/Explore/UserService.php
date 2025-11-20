<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\User;

class UserService
{
    private User $user;

    public function __construct(\PDO $pdo)
    {
        $this->user = new User($pdo);
    }

    public function createUser(array $data): int
    {
        return $this->user->insert($data);
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
            ->select(['*'])
            ->where('email', '=', $email)
            ->first();
    }
}
