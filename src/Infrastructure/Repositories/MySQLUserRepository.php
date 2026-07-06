<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use PDO;

class MySQLUserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByUid(string $uid): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE uid = :uid LIMIT 1");
        $stmt->execute(['uid' => $uid]);
        $row = $stmt->fetch();

        if ($row) {
            return new User(
                $row['uid'],
                $row['email'],
                $row['password'] ?? '',
                $row['display_name'],
                $row['role'],
                $row['created_at']
            );
        }

        return null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if ($row) {
            return new User(
                $row['uid'],
                $row['email'],
                $row['password'] ?? '',
                $row['display_name'],
                $row['role'],
                $row['created_at']
            );
        }

        return null;
    }

    public function save(User $user): bool
    {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            $stmt = $this->db->prepare("
                INSERT INTO users (uid, email, password, display_name, role, created_at)
                VALUES (:uid, :email, :password, :display_name, :role, :created_at)
                ON CONFLICT (uid) DO UPDATE SET
                    password = EXCLUDED.password,
                    display_name = EXCLUDED.display_name,
                    role = EXCLUDED.role
            ");
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO users (uid, email, password, display_name, role, created_at)
                VALUES (:uid, :email, :password, :display_name, :role, :created_at)
                ON DUPLICATE KEY UPDATE 
                    password = VALUES(password),
                    display_name = VALUES(display_name),
                    role = VALUES(role)
            ");
        }

        return $stmt->execute([
            'uid' => $user->getUid(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'display_name' => $user->getDisplayName(),
            'role' => $user->getRole(),
            'created_at' => $user->getCreatedAt()
        ]);
    }
}
