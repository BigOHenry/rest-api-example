<?php

declare(strict_types=1);

namespace App\Application\Bus\Query;

interface QueryBusInterface
{
    public function handle(QueryInterface $query): QueryResultInterface;
}
