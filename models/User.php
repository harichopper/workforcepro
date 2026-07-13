<?php
declare(strict_types=1);

class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );
    }

    public function updateLastLogin(int $id): void
    {
        $this->execute(
            'UPDATE users SET last_login = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public function updatePassword(int $id, string $hashed): bool
    {
        return $this->update($id, ['password' => $hashed]);
    }

    public function updateProfile(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }
}
