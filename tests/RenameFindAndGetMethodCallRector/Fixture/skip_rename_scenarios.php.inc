<?php

namespace Rector\Tests\TypeDeclaration\Rector\RenemameFindAndGetMethodCallRector\Fixture;

class UserRepository 
{
    // Should NOT rename: nullable return type
    public function findUserById(int $id): ?User
    {
        return null;
    }

    // Should NOT rename: primitive return type
    public function findCount(): int
    {
        return 0;
    }

    // Should NOT rename: primitive return type
    public function findUserName(): string
    {
        return '';
    }

    // Should NOT rename: array return type
    public function findUserData(): array
    {
        return [];
    }

    // Should NOT rename: no return type
    public function findSomething()
    {
        // do something
    }

    // Should NOT rename: doesn't start with "find"
    public function getUserById(int $id): User
    {
        return new User();
    }

    // Should NOT rename: doesn't start with "find"
    public function searchUserById(int $id): User
    {
        return new User();
    }

    // Should NOT rename: void return type
    public function findAndProcess(): void
    {
        // process something
    }

    // Should NOT rename: mixed return type
    public function findMixed(): mixed
    {
        return null;
    }

    // Should NOT rename: union type with null
    public function findUserOrNull(): User|null
    {
        return null;
    }

    // Should NOT rename: union type with multiple types
    public function findUserOrAdmin(): User|Admin
    {
        return new User();
    }

    // Should NOT rename: union type with primitive
    public function findUserOrFalse(): User|false
    {
        return false;
    }
}

class User {}
class Admin {}