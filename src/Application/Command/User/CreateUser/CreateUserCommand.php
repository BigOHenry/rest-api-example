<?php

declare(strict_types=1);

namespace App\Application\Command\User\CreateUser;

use App\Application\Bus\Command\CommandInterface;
use App\Domain\User\Exception\UserValidationDomainDomainException;
use App\Domain\User\Validator\UserValidator;
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
     * @throws UserValidationDomainDomainException
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
            throw new UserValidationDomainDomainException(message: 'Missing required fields: ' . implode(', ', $missingFields));
        }

        $errors = UserValidator::validateForCreation(
            email: $data['email'],
            name: $data['name'],
            role: $data['role'],
            password: $data['password']
        );

        if (!empty($errors)) {
            throw UserValidationDomainDomainException::withErrors(errors: $errors);
        }

        return new self(
            email: $data['email'],
            password: $data['password'],
            name: $data['name'],
            role: UserRole::from($data['role'])
        );
    }
}
