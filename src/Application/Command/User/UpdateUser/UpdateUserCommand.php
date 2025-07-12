<?php

declare(strict_types=1);

namespace App\Application\Command\User\UpdateUser;

use App\Application\Bus\Command\CommandInterface;
use App\Domain\User\ValueObject\UserRole;

final readonly class UpdateUserCommand implements CommandInterface
{
    public function __construct(
        public int $userId,
        public string $email,
        public ?string $name,
        public ?UserRole $role
    ) {}
}