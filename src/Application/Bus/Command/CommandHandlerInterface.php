<?php

declare(strict_types=1);

namespace App\Application\Bus\Command;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): void;
}
