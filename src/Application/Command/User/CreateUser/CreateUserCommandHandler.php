<?php

declare(strict_types=1);

namespace App\Application\Command\User\CreateUser;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Bus\Command\CreationCommandHandlerInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Exception\UserAlreadyExistsDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHashingServiceInterface;
use App\Domain\User\Service\UserAuthorizationService;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class CreateUserCommandHandler implements CreationCommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHashingServiceInterface $passwordHashingService,
        private UserAuthorizationService $userAuthorizationService,
        private Security $security,
    ) {
    }

    public function handle(CommandInterface $command): int
    {
        \assert($command instanceof CreateUserCommand);

        if (!$this->userAuthorizationService->canManageUsers(user: $this->security->getUser())) {
            throw UserAccessDeniedDomainException::forUserManagement();
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
