<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    /**
     * Cari user berdasarkan UID
     */
    public function findByUid(string $uid): ?User;

    /**
     * Cari user berdasarkan email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Simpan/update user
     */
    public function save(User $user): bool;
}
