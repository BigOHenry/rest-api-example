<?php

declare(strict_types=1);

namespace App\Application\Command\User\CreateUser;

use App\Application\Bus\Command\CommandInterface;
use App\Application\Exception\ValidationErrorException;
use App\Domain\User\ValueObject\UserRole;

final readonly class CreateUserCommand implements CommandInterface
{
    protected function __construct(
        public string $email,
        public string $password,
        public string $name,
        public UserRole $role,
    ) {
    }

    /**
     * @param array<string, string> $data
     *
     * @throws ValidationErrorException
     */
    public static function fromApiArray(array $data): self
    {
        $requiredFields = ['email', 'name', 'password', 'role'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            } else {
                $data[$field] = trim($data[$field]);
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

        if (
            mb_strlen($data['password']) < 8
            || !preg_match('/[A-Z]/', $data['password'])
            || !preg_match('/[a-z]/', $data['password'])
            || !preg_match('/[0-9]/', $data['password'])
            || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $data['password'])
        ) {
            $errors['password'] = 'Password must be at least 8 characters long, contains at least one'
                . ' lowercase and uppercase letter. one number and one special character.';
        }

        if (!empty($errors)) {
            throw ValidationErrorException::withErrors(errors: $errors);
        }

        return new self(
            email: $data['email'],
            password: $data['password'],
            name: $data['name'],
            role: UserRole::from($data['role'])
        );
    }
}
