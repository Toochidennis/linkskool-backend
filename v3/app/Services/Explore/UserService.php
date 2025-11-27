<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Traits\AuthenticatesRequests;
use V3\App\Models\Explore\User;

class UserService
{
    use AuthenticatesRequests;

    private User $user;

    public function __construct(\PDO $pdo)
    {
        $this->user = new User($pdo);
    }

    public function createUser(array $data): int
    {
        $payload = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? '',
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'roleId' => $data['role_id'] ?? 0,
            'accessLevel' => $data['access_level'] === 'staff' ? 2 : 1,
            'picture_ref' => $data['picture_ref'] ?? null,

        ];
        return $this->user->insert($payload);
    }

    public function updateUser(array $data): bool
    {
        $payload = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? '',
            'password' => isset($data['password']) ?
                password_hash($data['password'], PASSWORD_BCRYPT) :
                $this->user->where('id', '=', $data['id'])->first()['password'],
            'accessLevel' => $data['access_level'] === 'staff' ? 2 : 1,
            'roleId' => $data['role_id'] ?? 0,
            'picture_ref' => $data['picture_ref'] ?? null,
        ];

        return $this->user
            ->where('id', '=', $data['id'])
            ->update($payload);
    }

    public function getUserById(int $id): ?array
    {
        return $this->user
            ->where('id', '=', $id)
            ->first();
    }

    public function login(string $username, string $password): array
    {
        $user = $this->user
            ->select([
                'id',
                'first_name',
                'last_name',
                'username',
                'email',
                'accessLevel AS access_level',
                'roleId AS role_id',
                'password',
                'picture_ref'
            ])
            ->where('username', '=', $username)
            ->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return [];
        }

        return [
            'data' => $user,
            'token' => self::generateJWT(
                userId: $user['id'],
                name: $user['first_name'] . ' ' . $user['last_name'],
                role: $user['access_level'] === 2 ? 'staff' : 'admin'
            )
        ];
    }

    public function getUsers(): array
    {
        $users = $this->user
            ->select([
                'id',
                'first_name',
                'last_name',
                'username',
                'email',
                'accessLevel AS access_level',
                'roleId AS role_id',
                'picture_ref'
            ])
            ->where(function ($query) {
                $query->where('accessLevel', '=', 1)
                    ->orWhere('accessLevel', '=', 2);
            })
            ->get();

        return array_map(function ($user) {
            return [
                ...$user,
                'access_level' => $user['access_level'] === 2 ? 'staff' : 'admin',
            ];
        }, $users);
    }
}
