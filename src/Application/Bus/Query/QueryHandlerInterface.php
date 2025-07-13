<?php

declare(strict_types=1);

namespace App\Application\Bus\Query;

interface QueryHandlerInterface
{
    public function handle(QueryInterface $query): mixed;
}
