<?php

declare(strict_types=1);

namespace App\Application\Exception;

class ValidationErrorException extends \Exception
{
    /**
     * @param string[] $errors
     */
    public function __construct(string $message, private array $errors = [], int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * @param string[] $errors
     */
    public static function withErrors(array $errors): self
    {
        return new self(message: 'Invalid data', errors: $errors);
    }
}
