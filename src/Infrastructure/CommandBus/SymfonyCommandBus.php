<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus;

use App\Application\Bus\Command\CommandBusInterface;
use App\Application\Bus\Command\CommandHandlerInterface;
use App\Application\Bus\Command\CommandInterface;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;

class SymfonyCommandBus implements CommandBusInterface
{
    private CommandHandlerRegistry $registry;

    public function __construct(iterable $handlers)
    {
        $this->registry = new CommandHandlerRegistry();
        $this->registerHandlers($handlers);
    }

    private function dispatch(CommandInterface $command): void
    {
        $handler = $this->registry->get(get_class($command));

        if (!$handler) {
            throw new NoHandlerForMessageException(sprintf(
                'No handler for message "%s".',
                get_class($command)
            ));
        }

        $handler->handle($command);
    }

    public function handle(CommandInterface $command): void
    {
        $this->dispatch($command);
    }


    private function registerHandlers(iterable $handlers): void
    {
        foreach ($handlers as $handler) {
            if (!$handler instanceof CommandHandlerInterface) {
                continue;
            }

            $this->registerHandlerForSupportedCommands($handler);
        }
    }

    private function registerHandlerForSupportedCommands(CommandHandlerInterface $handler): void
    {
        $handlerClass = get_class($handler);
        $commandClass = $this->getCommandClassFromHandlerClass($handlerClass);

        if ($commandClass && class_exists($commandClass)) {
            $this->registry->register($commandClass, $handler);
        }
    }

    private function getCommandClassFromHandlerClass(string $handlerClass): ?string
    {
        if (str_ends_with($handlerClass, 'Handler')) {
            return substr($handlerClass, 0, -7);
        }

        return null;
    }
}

