<?php

declare(strict_types=1);

namespace App\Application\Query\User\GetUsers;

use App\Application\Bus\Query\QueryResultInterface;

readonly class GetUsersQueryResult implements QueryResultInterface
{
    /**
     * @param array<int, array{id: int, email: string, name: string, role: 'admin'|'author'|'reader'}> $users
     */
    public function __construct(
        public array $users,
    ) {
    }
}
