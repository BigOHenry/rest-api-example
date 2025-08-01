<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserRole;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * @return array<int, User>
     */
    public function findAllByRole(UserRole $role): array;

    /**
     * @return array<int, User>
     */
    public function findAll(): array;

    public function remove(User $user): void;
}
