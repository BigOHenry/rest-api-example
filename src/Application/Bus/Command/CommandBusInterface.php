<?php

namespace App\Application\Bus\Command;

interface CommandBusInterface
{
    public function handle(object $command): void;
}