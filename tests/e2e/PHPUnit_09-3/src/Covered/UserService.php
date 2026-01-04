<?php

namespace Infection\E2ETests\PHPUnit_09_3\Covered;

class UserService
{
    use LoggerTrait;

    private array $users = [];

    public function addUser(string $name, string $email): bool
    {
        if (empty($name) || empty($email)) {
            $this->log('Failed to add user: empty name or email');
            return false;
        }

        if ($this->userExists($email)) {
            $this->log("Failed to add user: email {$email} already exists");
            return false;
        }

        $this->users[$email] = ['name' => $name, 'email' => $email];
        $this->log("User {$name} added successfully");
        return true;
    }

    public function removeUser(string $email): bool
    {
        if (!$this->userExists($email)) {
            $this->log("Failed to remove user: email {$email} not found");
            return false;
        }

        unset($this->users[$email]);
        $this->log("User {$email} removed successfully");
        return true;
    }

    public function getUser(string $email): ?array
    {
        return $this->users[$email] ?? null;
    }

    public function userExists(string $email): bool
    {
        return isset($this->users[$email]);
    }

    public function getUserCount(): int
    {
        return count($this->users);
    }
}
