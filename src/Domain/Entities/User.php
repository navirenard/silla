<?php

/**
 * Domain Entity: User
 * 
 * Representasi pengguna dalam sistem antrian.
 * Entity ini tidak bergantung pada framework atau infrastruktur apapun.
 */

namespace App\Domain\Entities;

class User
{
    private string $uid;
    private string $email;
    private string $password;
    private string $displayName;
    private string $role;
    private ?string $createdAt;

    public function __construct(
        string $uid,
        string $email,
        string $password = '',
        string $displayName = '',
        string $role = 'officer',
        ?string $createdAt = null
    ) {
        $this->uid = $uid;
        $this->email = $email;
        $this->password = $password;
        $this->displayName = $displayName;
        $this->role = $role;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getUid(): string { return $this->uid; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getDisplayName(): string { return $this->displayName; }
    public function getRole(): string { return $this->role; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    // Setters
    public function setPassword(string $password): void { $this->password = $password; }
    public function setDisplayName(string $name): void { $this->displayName = $name; }
    public function setRole(string $role): void { $this->role = $role; }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Konversi ke array untuk penyimpanan
     */
    public function toArray(): array
    {
        return [
            'uid' => $this->uid,
            'email' => $this->email,
            'password' => $this->password,
            'displayName' => $this->displayName,
            'role' => $this->role,
            'createdAt' => $this->createdAt,
        ];
    }

    /**
     * Buat entity dari array data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['uid'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['displayName'] ?? '',
            $data['role'] ?? 'officer',
            $data['createdAt'] ?? null
        );
    }
}
