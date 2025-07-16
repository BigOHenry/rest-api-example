<?php

declare(strict_types=1);

namespace App\Application\Exception;

class HandlerNotFoundException extends \LogicException
{
    public function __construct(string $queryClass, int $code = 500, ?\Throwable $previous = null)
    {
        $message = \sprintf('No handler found for "%s".', $queryClass);
        parent::__construct($message, $code, $previous);
    }

    public static function forQuery(string $queryClass): self
    {
        return new self($queryClass);
    }

    public static function forCommand(string $commandClass): self
    {
        return new self($commandClass);
    }
}
