<?php

declare(strict_types=1);

namespace App\Infrastructure\QueryBus;

use App\Application\Bus\Query\QueryHandlerInterface;

class QueryHandlerRegistry
{
    /**
     * @var QueryHandlerInterface[]
     */
    private array $handlers = [];

    public function register(string $queryClass, QueryHandlerInterface $handler): void
    {
        $this->handlers[$queryClass] = $handler;
    }

    public function get(string $queryClass): ?QueryHandlerInterface
    {
        return $this->handlers[$queryClass] ?? null;
    }
}
