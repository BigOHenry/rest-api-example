<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use App\Application\Exception\HandlerNotFoundException;

class SymfonyCommandBus implements CommandBusInterface
{
    private CommandHandlerRegistry $registry;

    /**
     * @param CommandHandlerInterface[] $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->registry = new CommandHandlerRegistry();
        $this->registerHandlers(handlers: $handlers);
    }

    public function handle(CommandInterface $command): void
    {
        $this->dispatch(command: $command);
    }

    private function dispatch(CommandInterface $command): void
    {
        $handler = $this->registry->get(commandClass: $command::class);

        if (!$handler) {
            throw HandlerNotFoundException::forCommand(commandClass: $command::class);
        }

        $handler->handle(command: $command);
    }

    /**
     * @param CommandHandlerInterface[] $handlers
     */
    private function registerHandlers(iterable $handlers): void
    {
        foreach ($handlers as $handler) {
            if (!$handler instanceof CommandHandlerInterface) {
                continue;
            }

            $this->registerHandlerForSupportedCommands(handler: $handler);
        }
    }

    private function registerHandlerForSupportedCommands(CommandHandlerInterface $handler): void
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
