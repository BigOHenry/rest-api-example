<?php

declare(strict_types=1);

namespace App\Infrastructure\QueryBus;

use App\Application\Bus\Query\QueryBusInterface;
use App\Application\Bus\Query\QueryHandlerInterface;
use App\Application\Bus\Query\QueryInterface;
use App\Application\Bus\Query\QueryResultInterface;
use App\Application\Exception\HandlerNotFoundException;

final readonly class SymfonyQueryBus implements QueryBusInterface
{
    private QueryHandlerRegistry $registry;

    /**
     * @param QueryHandlerInterface[] $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->registry = new QueryHandlerRegistry();
        $this->registerHandlers($handlers);
    }

    public function handle(QueryInterface $query): QueryResultInterface
    {
        return $this->dispatch($query);
    }

    private function dispatch(QueryInterface $query): QueryResultInterface
    {
        $handler = $this->registry->get($query::class);

        if (!$handler) {
            throw HandlerNotFoundException::forQuery(queryClass: $query::class);
        }

        return $handler->handle($query);
    }

    /**
     * @param QueryHandlerInterface[] $handlers
     */
    private function registerHandlers(iterable $handlers): void
    {
        foreach ($handlers as $handler) {
            if (!$handler instanceof QueryHandlerInterface) {
                continue;
            }

            $this->registerHandlerForSupportedQueries($handler);
        }
    }

    private function registerHandlerForSupportedQueries(QueryHandlerInterface $handler): void
    {
        $handlerClass = $handler::class;
        $queryClass = $this->getQueryClassFromHandlerClass($handlerClass);

        if ($queryClass && class_exists($queryClass)) {
            $this->registry->register($queryClass, $handler);
        }
    }

    private function getQueryClassFromHandlerClass(string $handlerClass): ?string
    {
        if (str_ends_with(haystack: $handlerClass, needle: 'Handler')) {
            return mb_substr(string: $handlerClass, start: 0, length: -7);
        }

        return null;
    }
}
