<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

class UserNotFoundDomainException extends UserDomainException
{
    public static function withId(int $id): self
    {
        return new self(message: "User with id '{$id}' not found", code: 400);
    }

    public static function withEmail(string $email): self
    {
        return new self(message: "User with email '{$email}' not found", code: 400);
    }
}
