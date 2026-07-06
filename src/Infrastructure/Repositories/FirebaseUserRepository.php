<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Firebase\FirebaseDBClient;

class FirebaseUserRepository implements UserRepositoryInterface
{
    private FirebaseDBClient $dbClient;

    public function __construct(FirebaseDBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    public function findByUid(string $uid): ?User
    {
        $response = $this->dbClient->get("users/{$uid}");
        
        if ($response['success'] && !empty($response['data'])) {
            return User::fromArray($response['data']);
        }

        return null;
    }

    public function findByEmail(string $email): ?User
    {
        // Untuk menghindari error index Firebase di awal, kita ambil semua dan filter di PHP.
        // Skala pengguna sistem antrian ini sangat kecil (hanya admin & petugas).
        $response = $this->dbClient->get("users");
        
        if ($response['success'] && !empty($response['data'])) {
            foreach ($response['data'] as $uid => $userData) {
                if (isset($userData['email']) && strtolower($userData['email']) === strtolower($email)) {
                    return User::fromArray($userData);
                }
            }
        }

        return null;
    }

    public function save(User $user): bool
    {
        $response = $this->dbClient->set("users/{$user->getUid()}", $user->toArray());
        return $response['success'];
    }
}
