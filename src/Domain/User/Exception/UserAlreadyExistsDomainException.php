<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

class UserAlreadyExistsDomainException extends UserDomainException
{
    public static function withEmail(): self
    {
        return new self(message: 'User with this email already exists');
    }
}
