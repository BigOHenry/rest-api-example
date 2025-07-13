<?php

declare(strict_types=1);

namespace App\Application\Command\User\UpdateUser;

use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;

final readonly class UpdateUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(UpdateUserCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);
        if (!$user) {
            throw UserNotFoundException::withId($command->userId);
        }

        if ($command->email) {
            $user->setEmail($command->email);
        }

        if ($command->name) {
            $user->setName($command->name);
        }

        if ($command->role) {
            $user->setRole($command->role);
        }

        $this->userRepository->save($user);
    }
}
