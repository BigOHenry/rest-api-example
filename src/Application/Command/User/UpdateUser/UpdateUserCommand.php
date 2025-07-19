<?php

declare(strict_types=1);

namespace App\Application\Command\User\UpdateUser;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Exception\ValidationErrorException;
use App\Domain\User\Validator\UserValidator;
use App\Domain\User\ValueObject\UserRole;

final readonly class UpdateUserCommand implements CommandInterface
{
    protected function __construct(
        public int $id,
        public string $email,
        public string $name,
        public UserRole $role,
    ) {
    }

    /**
     * @param array<string, string> $data
     *
     * @throws ValidationErrorException
     */
    public static function fromApiArray(int $userId, array $data): self
    {
        $requiredFields = ['email', 'name', 'role'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new ValidationErrorException(message: 'Missing required fields: ' . implode(', ', $missingFields));
        }

        $errors = UserValidator::validateForUpdate(
            email: $data['email'],
            name: $data['name'],
            role: $data['role'],
        );

        if (!empty($errors)) {
            throw ValidationErrorException::withErrors(errors: $errors);
        }

        return new self(
            id: $userId,
            email: trim($data['email']),
            name: trim($data['name']),
            role: UserRole::from($data['role'])
        );
    }
}
