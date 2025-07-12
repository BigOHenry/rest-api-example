<?php

declare (strict_types = 1);

namespace App\Infrastructure\CommandBus\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ValidationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $command = $envelope->getMessage();

        $violations = $this->validator->validate($command);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
