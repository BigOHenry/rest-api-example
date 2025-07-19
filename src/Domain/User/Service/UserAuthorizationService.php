<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

use App\Domain\User\ValueObject\UserRole;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAuthorizationService
{
    public function canManageUsers(?UserInterface $user): bool
    {
        if ($user === null) {
            return false;
        }

        return \in_array(UserRole::ADMIN->value, $user->getRoles(), true);
    }

    public function canReadUsers(?UserInterface $user): bool
    {
        if ($user === null) {
            return false;
        }

        return \in_array(UserRole::ADMIN->value, $user->getRoles(), true);
    }
}
