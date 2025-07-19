<?php

declare(strict_types=1);

namespace App\Application\Query\User\GetUser;

use App\Application\Bus\Query\QueryHandlerInterface;
use App\Application\Bus\Query\QueryInterface;
use App\Domain\User\Exception\UserAccessDeniedDomainException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\UserAuthorizationService;
use Symfony\Bundle\SecurityBundle\Security;

readonly class GetUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationService $userAuthorizationService,
        private Security $security,
    ) {
    }

    public function handle(QueryInterface $query): GetUserQueryResult
    {
        \assert($query instanceof GetUserQuery);

        if (!$this->userAuthorizationService->canReadUsers(user: $this->security->getUser())) {
            throw UserAccessDeniedDomainException::forUserReading();
        }

        return new GetUserQueryResult(user: $this->userRepository->findById($query->id));
    }
}
