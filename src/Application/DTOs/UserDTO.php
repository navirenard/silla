<?php

namespace App\Application\DTOs;

class UserDTO
{
    public string $uid;
    public string $email;
    public string $displayName;
    public string $role;
    public ?string $createdAt;

    public function __construct(string $uid, string $email, string $displayName, string $role, ?string $createdAt = null)
    {
        $this->uid = $uid;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->role = $role;
        $this->createdAt = $createdAt;
    }

    public static function fromEntity($user): self
    {
        return new self(
            $user->getUid(),
            $user->getEmail(),
            $user->getDisplayName(),
            $user->getRole(),
            $user->getCreatedAt()
        );
    }
}
