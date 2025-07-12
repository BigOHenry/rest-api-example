<?php

declare(strict_types=1);

namespace App\Application\Command\User\DeleteUser;

use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;

final readonly class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(DeleteUserCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);
        if (!$user) {
            throw UserNotFoundException::withId($command->userId);
        }

        $this->userRepository->remove($user);
    }
}