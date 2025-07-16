<?php

declare(strict_types=1);

namespace App\Application\Command\User\CreateUser;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Bus\Command\CreationCommandHandlerInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHashingServiceInterface;

readonly class CreateUserCommandHandler implements CreationCommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHashingServiceInterface $passwordHashingService,
    ) {
    }

    public function handle(CommandInterface $command): int
    {
        \assert($command instanceof CreateUserCommand);

        $existingUser = $this->userRepository->findByEmail($command->email);
        if ($existingUser) {
            throw UserAlreadyExistsException::withEmail();
        }

        $user = User::create(
            $command->email,
            'tmp',
            $command->name,
            $command->role
        );
        $hashedPassword = $this->passwordHashingService->hashPassword($user, $command->password);

        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        return (int) $user->getId();
    }
}
