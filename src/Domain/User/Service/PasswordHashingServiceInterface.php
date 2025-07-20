<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface PasswordHashingServiceInterface
{
    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $password): string;

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $password): bool;
}
