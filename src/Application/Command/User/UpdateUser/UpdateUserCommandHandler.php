<?php

declare(strict_types=1);

namespace App\Application\Command\User\UpdateUser;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;


final readonly class UpdateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof UpdateUserCommand);

        $user = $this->userRepository->findById($command->id);
        if (!$user) {
            throw UserNotFoundException::withId($command->id);
        }

        $user->setEmail($command->email);
        $user->setName($command->name);
        $user->setRole($command->role);

        $this->userRepository->save($user);
    }
}
