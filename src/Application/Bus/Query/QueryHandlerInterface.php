<?php

namespace App\Application\Bus\Query;

interface QueryHandlerInterface
{
    public function handle(QueryInterface $query): mixed;
}