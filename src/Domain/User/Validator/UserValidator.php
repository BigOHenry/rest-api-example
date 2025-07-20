<?php

declare(strict_types=1);

namespace App\Domain\User\Validator;

use App\Domain\User\ValueObject\UserRole;

class UserValidator
{
    /**
     * @return array<string, string>
     */
    public static function validateEmail(string $email): array
    {
        $errors = [];

        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function validateName(string $name): array
    {
        $errors = [];

        if (mb_strlen(trim($name)) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function validateRole(string $role): array
    {
        $errors = [];

        $validRoles = array_column(UserRole::cases(), 'value');
        if (!\in_array($role, $validRoles, true)) {
            $errors['role'] = 'Invalid role value. Must be one of: ' . implode(', ', $validRoles);
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];

        if (
            mb_strlen($password) < 8
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/[0-9]/', $password)
            || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)
        ) {
            $errors['password'] = 'Password must be at least 8 characters long, contains at least one'
                . ' lowercase and uppercase letter, one number and one special character.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function validateForCreation(string $email, string $name, string $role, string $password): array
    {
        return array_merge(
            self::validateEmail(email: $email),
            self::validateName(name: $name),
            self::validateRole(role: $role),
            self::validatePassword(password: $password)
        );
    }

    /**
     * @return array<string, string>
     */
    public static function validateForUpdate(string $email, string $name, string $role): array
    {
        return array_merge(
            self::validateEmail(email: $email),
            self::validateName(name: $name),
            self::validateRole(role: $role)
        );
    }
}
