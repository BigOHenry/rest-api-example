<?php

namespace App\Application\Bus\Command;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): void;
}