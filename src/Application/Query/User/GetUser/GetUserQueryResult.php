<?php

declare(strict_types=1);

namespace App\Application\Query\User\GetUser;

use App\Application\Bus\Query\QueryResultInterface;
use App\Domain\User\Entity\User;

readonly class GetUserQueryResult implements QueryResultInterface
{
    public function __construct(
        private ?User $user,
    ) {
    }

    public function isUserFound(): bool
    {
        return $this->user instanceof User;
    }

    /**
     * @return array<string, string|int>|null
     */
    public function toArray(): ?array
    {
        if ($this->user === null) {
            return null;
        }

        return [
            'id' => (int) $this->user->getId(),
            'email' => $this->user->getEmail(),
            'name' => $this->user->getName(),
            'role' => $this->user->getRole()->value,
        ];
    }
}
