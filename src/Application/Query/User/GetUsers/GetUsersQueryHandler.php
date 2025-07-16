<?php

declare(strict_types=1);

namespace App\Application\Query\User\GetUsers;

use App\Application\Bus\Query\QueryHandlerInterface;
use App\Application\Bus\Query\QueryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;

readonly class GetUsersQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(QueryInterface $query): GetUsersQueryResult
    {
        return new GetUsersQueryResult(users: $this->userRepository->findAll());
    }
}
