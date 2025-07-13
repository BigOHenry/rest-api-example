<?php declare(strict_types=1);

namespace App\Infrastructure\CommandBus;

use App\Application\Bus\Command\CommandHandlerInterface;

class CommandHandlerRegistry
{
    private array $handlers = [];

    public function register(string $commandClass, CommandHandlerInterface $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    public function get(string $commandClass): ?CommandHandlerInterface
    {
        return $this->handlers[$commandClass] ?? null;
    }
}
