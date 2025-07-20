<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User;

use App\Domain\User\Validator\UserValidator;
use PHPUnit\Framework\TestCase;

class UserValidatorTest extends TestCase
{
    // ========== Email Validation Tests ==========

    public function testValidateEmailWithValidEmail(): void
    {
        $errors = UserValidator::validateEmail('valid@example.com');

        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideValidateEmailWithVariousValidEmailsCases
     */
    public function testValidateEmailWithVariousValidEmails(string $email): void
    {
        $errors = UserValidator::validateEmail($email);

        $this->assertEmpty($errors);
    }

    public static function provideValidateEmailWithVariousValidEmailsCases(): iterable
    {
        return [
            'simple email' => ['test@example.com'],
            'email with subdomain' => ['user@mail.example.com'],
            'email with plus' => ['user+tag@example.com'],
            'email with numbers' => ['user123@example123.com'],
            'email with dash' => ['user-name@example-domain.com'],
        ];
    }

    /**
     * @dataProvider provideValidateEmailWithInvalidEmailsCases
     */
    public function testValidateEmailWithInvalidEmails(string $email): void
    {
        $errors = UserValidator::validateEmail($email);

        $this->assertArrayHasKey('email', $errors);
        $this->assertSame('Invalid email format', $errors['email']);
    }

    public static function provideValidateEmailWithInvalidEmailsCases(): iterable
    {
        return [
            'missing @' => ['invalid-email'],
            'missing domain' => ['invalid@'],
            'missing local part' => ['@example.com'],
            'double @' => ['test@@example.com'],
            'spaces in email' => ['test @example.com'],
            'invalid characters' => ['test<>@example.com'],
            'empty string' => [''],
        ];
    }

    // ========== Name Validation Tests ==========

    public function testValidateNameWithValidName(): void
    {
        $errors = UserValidator::validateName('Valid Name');

        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideValidateNameWithVariousValidNamesCases
     */
    public function testValidateNameWithVariousValidNames(string $name): void
    {
        $errors = UserValidator::validateName($name);

        $this->assertEmpty($errors);
    }

    public static function provideValidateNameWithVariousValidNamesCases(): iterable
    {
        return [
            'two characters' => ['Ab'],
            'normal name' => ['John Doe'],
            'long name' => ['Very Long Name With Many Words'],
            'name with accents' => ['José María'],
            'name with spaces' => ['  Valid Name  '],
        ];
    }

    public function testValidateNameWithTooShortName(): void
    {
        $shortName = 'A';

        $errors = UserValidator::validateName($shortName);

        $this->assertArrayHasKey('name', $errors);
        $this->assertSame('Name must be at least 2 characters long', $errors['name']);
    }

    /**
     * @dataProvider provideValidateNameWithInvalidNamesCases
     */
    public function testValidateNameWithInvalidNames(string $name): void
    {
        $errors = UserValidator::validateName($name);

        $this->assertArrayHasKey('name', $errors);
        $this->assertSame('Name must be at least 2 characters long', $errors['name']);
    }

    public static function provideValidateNameWithInvalidNamesCases(): iterable
    {
        return [
            'single character' => ['A'],
            'empty string' => [''],
            'only spaces' => ['   '], // trims to empty
            'single space' => [' '],
        ];
    }

    // ========== Role Validation Tests ==========

    public function testValidateRoleWithValidRoles(): void
    {
        // Act & Assert
        $this->assertEmpty(UserValidator::validateRole('ROLE_READER'));
        $this->assertEmpty(UserValidator::validateRole('ROLE_AUTHOR'));
        $this->assertEmpty(UserValidator::validateRole('ROLE_ADMIN'));
    }

    /**
     * @dataProvider provideValidateRoleWithAllValidRolesCases
     */
    public function testValidateRoleWithAllValidRoles(string $role): void
    {
        $errors = UserValidator::validateRole($role);

        $this->assertEmpty($errors);
    }

    public static function provideValidateRoleWithAllValidRolesCases(): iterable
    {
        return [
            'reader role' => ['ROLE_READER'],
            'author role' => ['ROLE_AUTHOR'],
            'admin role' => ['ROLE_ADMIN'],
        ];
    }

    /**
     * @dataProvider provideValidateRoleWithInvalidRolesCases
     */
    public function testValidateRoleWithInvalidRoles(string $role): void
    {
        // Act
        $errors = UserValidator::validateRole($role);

        // Assert
        $this->assertArrayHasKey('role', $errors);
        $this->assertStringContainsString('Invalid role value. Must be one of:', $errors['role']);
        $this->assertStringContainsString('ROLE_ADMIN, ROLE_AUTHOR, ROLE_READER', $errors['role']);
    }

    public static function provideValidateRoleWithInvalidRolesCases(): iterable
    {
        return [
            'invalid role' => ['ROLE_INVALID'],
            'empty string' => [''],
            'random text' => ['some_random_role'],
            'case mismatch' => ['role_reader'],
            'missing prefix' => ['READER'],
        ];
    }

    // ========== Password Validation Tests ==========

    public function testValidatePasswordWithValidPassword(): void
    {
        $errors = UserValidator::validatePassword('ValidPass123!');

        $this->assertEmpty($errors);
    }

    /**
     * @dataProvider provideValidatePasswordWithVariousValidPasswordsCases
     */
    public function testValidatePasswordWithVariousValidPasswords(string $password): void
    {
        $errors = UserValidator::validatePassword($password);

        $this->assertEmpty($errors);
    }

    public static function provideValidatePasswordWithVariousValidPasswordsCases(): iterable
    {
        return [
            'standard password' => ['Password123!'],
            'with special chars' => ['MyP@ssw0rd#'],
            'longer password' => ['ThisIsAVeryLongPassword123!'],
            'all special chars' => ['Aa1!@#$%^&*(),.?":{}|<>'],
            'minimum valid' => ['Aa1!bcde'],
        ];
    }

    /**
     * @dataProvider provideValidatePasswordWithInvalidPasswordsCases
     */
    public function testValidatePasswordWithInvalidPasswords(string $password, string $reason): void
    {
        // Act
        $errors = UserValidator::validatePassword($password);

        // Assert
        $this->assertArrayHasKey('password', $errors);
        $this->assertStringContainsString('Password must be at least 8 characters long', $errors['password']);
    }

    public static function provideValidatePasswordWithInvalidPasswordsCases(): iterable
    {
        return [
            'too short' => ['Pass1!', 'less than 8 characters'],
            'no uppercase' => ['password123!', 'missing uppercase letter'],
            'no lowercase' => ['PASSWORD123!', 'missing lowercase letter'],
            'no numbers' => ['Password!', 'missing number'],
            'no special chars' => ['Password123', 'missing special character'],
            'empty string' => ['', 'empty password'],
            'only letters' => ['Password', 'missing numbers and special chars'],
        ];
    }

    // ========== Combined Validation Tests ==========

    public function testValidateForCreationWithAllValidData(): void
    {
        $errors = UserValidator::validateForCreation(
            'valid@example.com',
            'Valid User',
            'ROLE_READER',
            'ValidPass123!'
        );

        $this->assertEmpty($errors);
    }

    public function testValidateForCreationWithMultipleErrors(): void
    {
        $errors = UserValidator::validateForCreation(
            'invalid-email',
            'A',
            'INVALID_ROLE',
            'weak'
        );

        $this->assertCount(4, $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('role', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    public function testValidateForUpdateWithAllValidData(): void
    {
        $errors = UserValidator::validateForUpdate(
            'updated@example.com',
            'Updated User',
            'ROLE_AUTHOR'
        );

        $this->assertEmpty($errors);
    }

    public function testValidateForUpdateWithMultipleErrors(): void
    {
        $errors = UserValidator::validateForUpdate(
            'invalid-email',
            'A',
            'INVALID_ROLE'
        );

        $this->assertCount(3, $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('role', $errors);
        // Password should not be present in update validation
        $this->assertArrayNotHasKey('password', $errors);
    }

    public function testValidateForUpdateDoesNotValidatePassword(): void
    {
        $errors = UserValidator::validateForUpdate(
            'valid@example.com',
            'Valid User',
            'ROLE_READER'
        );

        // Assert - update validation should not include password
        $this->assertEmpty($errors);
        $this->assertArrayNotHasKey('password', $errors);
    }
}
