<?php

declare(strict_types=1);

namespace App\Application\Command\User\CreateUser;

use App\Application\Bus\Command\CommandInterface;
use App\Domain\User\ValueObject\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserCommand implements CommandInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public string $password,
        #[Assert\NotBlank]
        public string $name,
        #[Assert\Choice([UserRole::ADMIN, UserRole::AUTHOR, UserRole::READER])]
        public UserRole $role,
    ) {
    }
}
