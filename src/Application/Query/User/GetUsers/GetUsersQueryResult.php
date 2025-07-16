<?php

declare(strict_types=1);

namespace App\Application\Query\User\GetUsers;

use App\Application\Bus\Query\QueryResultInterface;
use App\Domain\User\Entity\User;

readonly class GetUsersQueryResult implements QueryResultInterface
{
    /**
     * @param User[] $users
     */
    public function __construct(
        private array $users,
    ) {
    }

    public function count(): int
    {
        return \count($this->users);
    }

    /**
     * @return array<array<string, int|string>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn ($user) => [
                'id' => (int) $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'role' => $user->getRole()->value,
            ],
            $this->users
        );
    }
}
