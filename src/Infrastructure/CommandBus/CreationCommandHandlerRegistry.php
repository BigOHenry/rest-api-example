<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus;

use App\Application\Bus\Command\CreationCommandHandlerInterface;

class CreationCommandHandlerRegistry
{
    /**
     * @var CreationCommandHandlerInterface[]
     */
    private array $handlers = [];

    public function register(string $commandClass, CreationCommandHandlerInterface $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    public function get(string $commandClass): ?CreationCommandHandlerInterface
    {
        return $this->handlers[$commandClass] ?? null;
    }
}
