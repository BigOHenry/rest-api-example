<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

class UserAccessDeniedDomainException extends UserDomainException
{
    public static function forRegistrationWhenAdminExists(): self
    {
        return new self(
            message: 'Only the first administrator can be registered, other administrators must be created by a logged-in administrator',
            code: 403
        );
    }

    public static function forUserManagement(): self
    {
        return new self(message: 'User has no permission to manage users', code: 403);
    }

    public static function forUserReading(): self
    {
        return new self(message: 'User has no permission to read users', code: 403);
    }
}
