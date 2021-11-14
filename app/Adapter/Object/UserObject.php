<?php
declare(strict_types=1);

namespace App\Adapter\Object;

class UserObject
{
    private array $user;

    public function __construct(array $users)
    {
        $this->setUser($users);
    }

    private function setUser(array $user) {
        $this->user = $user;
    }

    public function getId(): string {
        return $this->user['id'] ?? '';
    }

    public function getEmail(): string {
        return $this->user['email'] ?? '';
    }

    public function getFirstName(): string {
        return $this->user['firstname'] ?? '';
    }

    public function getLastName(): string {
        return $this->user['lastname'] ?? '';
    }

    public function getStatus(): string {
        return $this->user['status'] ?? '';
    }
}