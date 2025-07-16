<?php

declare(strict_types=1);

namespace App\Application\Bus\Command;

interface CreationCommandBusInterface
{
    public function handle(CommandInterface $command): int;
}
