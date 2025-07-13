<?php

declare(strict_types=1);

namespace App\Infrastructure\QueryBus;

use App\Application\Bus\Query\QueryBusInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class SymfonyQueryBus implements QueryBusInterface
{
    public function __construct(
        private MessageBusInterface $queryBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function handle(object $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);

        return $envelope->last(HandledStamp::class)?->getResult();
    }
}
