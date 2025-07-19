<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Bus\Command\CreationCommandBusInterface;
use App\Application\Bus\Command\CreationCommandHandlerInterface;
use App\Application\Exception\HandlerNotFoundException;

class SymfonyCreationCommandBus implements CreationCommandBusInterface
{
    private CreationCommandHandlerRegistry $registry;

    /**
     * @param CreationCommandHandlerInterface[] $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->registry = new CreationCommandHandlerRegistry();
        $this->registerHandlers(handlers: $handlers);
    }

    public function handle(CommandInterface $command): int
    {
        return $this->dispatch(command: $command);
    }

    private function dispatch(CommandInterface $command): int
    {
        $handler = $this->registry->get(commandClass: $command::class);

        if (!$handler) {
            throw HandlerNotFoundException::forCommand(commandClass: $command::class);
        }

        return $handler->handle(command: $command);
    }

    /**
     * @param CreationCommandHandlerInterface[] $handlers
     */
    private function registerHandlers(iterable $handlers): void
    {
        foreach ($handlers as $handler) {
            if (!$handler instanceof CreationCommandHandlerInterface) {
                continue;
            }

            $this->registerHandlerForSupportedCommands(handler: $handler);
        }
    }

    private function registerHandlerForSupportedCommands(CreationCommandHandlerInterface $handler): void
    {
        $handlerClass = $handler::class;
        $commandClass = $this->getCommandClassFromHandlerClass(handlerClass: $handlerClass);

        if ($commandClass && class_exists(class: $commandClass)) {
            $this->registry->register(commandClass: $commandClass, handler: $handler);
        }
    }

    private function getCommandClassFromHandlerClass(string $handlerClass): ?string
    {
        if (str_ends_with(haystack: $handlerClass, needle: 'Handler')) {
            return mb_substr(string: $handlerClass, start: 0, length: -7);
        }

        return null;
    }
}
