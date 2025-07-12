<?php

namespace App\Application\Bus\Query;

interface QueryBusInterface
{
    public function handle(object $query): mixed;
}