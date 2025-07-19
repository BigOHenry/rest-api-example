<?php

declare(strict_types=1);

namespace App\Application\Command\User\DeleteUser;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserNotFoundDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserAuthorizationService;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class DeleteUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationService $userAuthorizationService,
        private Security $security,
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof DeleteUserCommand);

        if (!$this->userAuthorizationService->canManageUsers(user: $this->security->getUser())) {
            throw UserAccessDeniedDomainException::forUserManagement();
        }

        $user = $this->userRepository->findById(id: $command->id);
        if (!$user) {
            throw UserNotFoundDomainException::withId(id: $command->id);
        }

        $this->userRepository->remove(user: $user);
    }
}
