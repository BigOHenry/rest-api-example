<?php

declare(strict_types=1);

namespace App\Application\Command\User\DeleteUser;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;

final readonly class DeleteUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof DeleteUserCommand);

        $user = $this->userRepository->findById($command->id);
        if (!$user) {
            throw UserNotFoundException::withId($command->id);
        }

        $this->userRepository->remove($user);
    }
}
