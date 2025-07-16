<?php

declare(strict_types=1);

namespace App\Application\Command\User\UpdateUser;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Exception\ValidationErrorException;
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
            throw new ValidationErrorException('Missing required fields: ' . implode(', ', $missingFields));
        }

        $errors = [];

        if (!filter_var($data['email'], \FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (mb_strlen(trim($data['name'])) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }

        $validRoles = array_column(UserRole::cases(), 'value');
        if (!\in_array($data['role'], $validRoles, true)) {
            $errors['role'] = 'Invalid role value. Must be one of: ' . implode(', ', $validRoles);
        }

        if (!empty($errors)) {
            throw ValidationErrorException::withErrors($errors);
        }

        return new self(
            id: $userId,
            email: trim($data['email']),
            name: trim($data['name']),
            role: UserRole::from($data['role'])
        );
    }
}
