<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\User\Service\PasswordHashingServiceInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class PasswordHashingService implements PasswordHashingServiceInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $password): string
    {
        return $this->passwordHasher->hashPassword($user, $password);
    }

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }
}
