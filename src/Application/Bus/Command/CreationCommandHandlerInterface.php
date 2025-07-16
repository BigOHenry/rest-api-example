<?php

declare(strict_types=1);

namespace App\Application\Bus\Command;

interface CreationCommandHandlerInterface
{
    public function handle(CommandInterface $command): int;
}
