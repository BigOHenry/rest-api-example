<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

class UserNotFoundException extends UserException
{
    public static function withId(int $id): self
    {
        return new self("User with id '{$id}' not found");
    }

    public static function withEmail(string $email): self
    {
        return new self("User with email '{$email}' not found");
    }
}
