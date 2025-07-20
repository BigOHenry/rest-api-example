<?php

declare(strict_types=1);

namespace App\Application\Command\User\UpdateUser;

use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserNotFoundDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserAuthorizationService;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class UpdateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationService $userAuthorizationService,
        private Security $security,
    ) {
    }

    public function handle(CommandInterface $command): void
    {
        \assert($command instanceof UpdateUserCommand);

        if (!$this->userAuthorizationService->canManageUsers(user: $this->security->getUser())) {
            throw UserAccessDeniedDomainException::forUserManagement();
        }

        $user = $this->userRepository->findById(id: $command->id);
        if (!$user) {
            throw UserNotFoundDomainException::withId(id: $command->id);
        }

        $user->setEmail(email: $command->email);
        $user->setName(name: $command->name);
        $user->setRole(role: $command->role);

        $this->userRepository->save(user: $user);
    }
}
