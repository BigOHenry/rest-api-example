<?php

declare(strict_types=1);

namespace App\Application\Query\User\GetUser;

use App\Application\Bus\Query\QueryHandlerInterface;
use App\Application\Bus\Query\QueryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;

readonly class GetUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(QueryInterface $query): GetUserQueryResult
    {
        \assert($query instanceof GetUserQuery);

        return new GetUserQueryResult(user: $this->userRepository->findById($query->id));
    }
}
