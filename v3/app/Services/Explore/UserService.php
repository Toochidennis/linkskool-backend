<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Traits\AuthenticatesRequests;
use V3\App\Models\Explore\AuditLog;
use V3\App\Models\Explore\User;

class UserService
{
    use AuthenticatesRequests;

    private User $user;
    private AuditLog $auditLog;

    public function __construct(\PDO $pdo)
    {
        $this->user = new User($pdo);
        $this->auditLog = new AuditLog($pdo);
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
        $this->logAction(
            action: 'create_user',
            userId: $data['creator_id'],
            username: 'User ID ' . $data['creator_id'],
            details: 'Created user: ' . $data['username']
        );
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

        $this->logAction(
            action: 'user_login',
            userId: $user['id'],
            username: $user['username'],
            details: 'User logged in'
        );

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

    public function deleteUser(int $id): bool
    {
        $this->logAction(
            action: 'delete_user',
            userId: $id,
            username: "User ID $id",
            details: "Deleted user with ID: $id"
        );

        return $this->user
            ->where('id', '=', $id)
            ->delete();
    }

    private function logAction(string $action, int $userId, string $username, ?string $details = null): void
    {
        $payload = [
            'action' => $action,
            'user_id' => $userId,
            'username' => $username,
            'details' => $details,
        ];

        $this->auditLog->insert($payload);
    }
}
