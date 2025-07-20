<?php

declare(strict_types=1);

namespace App\Application\Query\User\GetUsers;

use App\Application\Bus\Query\QueryHandlerInterface;
use App\Application\Bus\Query\QueryInterface;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserAuthorizationService;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class GetUsersQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationService $userAuthorizationService,
        private Security $security,
    ) {
    }

    public function handle(QueryInterface $query): GetUsersQueryResult
    {
        if (!$this->userAuthorizationService->canReadUsers(user: $this->security->getUser())) {
            throw UserAccessDeniedDomainException::forUserReading();
        }

        return new GetUsersQueryResult(users: $this->userRepository->findAll());
    }
}
