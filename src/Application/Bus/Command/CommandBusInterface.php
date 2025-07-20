<?php

declare(strict_types=1);

namespace App\Application\Bus\Command;

use App\Application\Exception\HandleProcessErrorException;

interface CommandBusInterface
{
    /**
     * @throws HandleProcessErrorException
     */
    public function handle(CommandInterface $command): void;
}
