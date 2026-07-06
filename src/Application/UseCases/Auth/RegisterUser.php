<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Firebase\FirebaseAuthClient;
use App\Application\DTOs\UserDTO;
use Exception;

class RegisterUser
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Mendaftarkan pengguna baru secara lokal ke MySQL.
     */
    public function execute(string $name, string $email, string $password, string $role = 'officer'): UserDTO
    {
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("Semua field (Nama, Email, Password) wajib diisi.");
        }

        if (strlen($password) < 6) {
            throw new Exception("Password minimal harus 6 karakter.");
        }

        // 1. Cek apakah email sudah terdaftar di database
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            throw new Exception("Alamat email sudah terdaftar.");
        }

        // 2. Hash password secara aman menggunakan BCRYPT
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // 3. Generate UID unik lokal
        $uid = uniqid('u_', true);

        // 4. Simpan ke database
        $user = new User($uid, $email, $hashedPassword, $name, $role);
        $saved = $this->userRepository->save($user);

        if (!$saved) {
            throw new Exception("Gagal menyimpan profil pengguna ke database.");
        }

        return UserDTO::fromEntity($user);
    }
}
