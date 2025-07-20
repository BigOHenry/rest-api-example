<?php

declare(strict_types=1);

namespace App\Application\Command\User\RegisterUser;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Bus\Command\CreationCommandHandlerInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserAlreadyExistsDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHashingServiceInterface;
use App\Domain\User\ValueObject\UserRole;

readonly class RegisterUserCommandHandler implements CreationCommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHashingServiceInterface $passwordHashingService,
    ) {
    }

    public function handle(CommandInterface $command): int
    {
        \assert($command instanceof RegisterUserCommand);

        if ($command->role === UserRole::ADMIN) {
            $found = $this->userRepository->findAllByRole(role: UserRole::ADMIN);

            if (\count($found) > 0) {
                throw UserAccessDeniedDomainException::forRegistrationWhenAdminExists();
            }
        }

        $existingUser = $this->userRepository->findByEmail(email: $command->email);
        if ($existingUser) {
            throw UserAlreadyExistsDomainException::withEmail();
        }

        $user = User::create(
            email: $command->email,
            password: 'tmp',
            name: $command->name,
            role: $command->role
        );

        $user->setPassword(password: $this->passwordHashingService->hashPassword(user: $user, password: $command->password));

        $this->userRepository->save(user: $user);

        return (int) $user->getId();
    }
}
