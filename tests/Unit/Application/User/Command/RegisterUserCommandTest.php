<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User\Command;

use App\Application\Command\User\RegisterUser\RegisterUserCommand;
use App\Domain\User\Exception\UserValidationDomainDomainException;
use App\Domain\User\ValueObject\UserRole;
use PHPUnit\Framework\TestCase;

class RegisterUserCommandTest extends TestCase
{
    public function testFromApiArrayWithValidReaderData(): void
    {
        $data = [
            'email' => 'reader@example.com',
            'password' => 'SecurePass123!',
            'name' => 'Test Reader',
            'role' => 'ROLE_READER',
        ];

        $command = RegisterUserCommand::fromApiArray($data);

        $this->assertSame('reader@example.com', $command->email);
        $this->assertSame('SecurePass123!', $command->password);
        $this->assertSame('Test Reader', $command->name);
        $this->assertSame(UserRole::READER, $command->role);
    }

    public function testFromApiArrayWithValidAuthorData(): void
    {
        $data = [
            'email' => 'author@example.com',
            'password' => 'AuthorPass123!',
            'name' => 'Test Author',
            'role' => 'ROLE_AUTHOR',
        ];

        $command = RegisterUserCommand::fromApiArray($data);

        $this->assertSame('author@example.com', $command->email);
        $this->assertSame('ROLE_AUTHOR', $command->role->value);
    }

    public function testFromApiArrayWithValidAdminData(): void
    {
        $data = [
            'email' => 'admin@example.com',
            'password' => 'AdminPass123!',
            'name' => 'Test Admin',
            'role' => 'ROLE_ADMIN',
        ];

        $command = RegisterUserCommand::fromApiArray($data);

        $this->assertSame(UserRole::ADMIN, $command->role);
    }

    public function testFromApiArrayTrimsWhitespace(): void
    {
        $data = [
            'email' => '  trimmed@example.com  ',
            'password' => '  TrimPass123!  ',
            'name' => '  Trimmed User  ',
            'role' => '  ROLE_READER  ',
        ];

        $command = RegisterUserCommand::fromApiArray($data);

        $this->assertSame('trimmed@example.com', $command->email);
        $this->assertSame('TrimPass123!', $command->password);
        $this->assertSame('Trimmed User', $command->name);
    }

    /**
     * @dataProvider provideFromApiArrayFailsWithMissingFieldsCases
     */
    public function testFromApiArrayFailsWithMissingFields(array $data, string $expectedMessage): void
    {
        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage($expectedMessage);

        RegisterUserCommand::fromApiArray($data);
    }

    public static function provideFromApiArrayFailsWithMissingFieldsCases(): iterable
    {
        return [
            'missing email' => [
                ['password' => 'Pass123!', 'name' => 'User', 'role' => 'ROLE_READER'],
                'Missing required fields: email',
            ],
            'missing password' => [
                ['email' => 'test@example.com', 'name' => 'User', 'role' => 'ROLE_READER'],
                'Missing required fields: password',
            ],
            'missing name' => [
                ['email' => 'test@example.com', 'password' => 'Pass123!', 'role' => 'ROLE_READER'],
                'Missing required fields: name',
            ],
            'missing role' => [
                ['email' => 'test@example.com', 'password' => 'Pass123!', 'name' => 'User'],
                'Missing required fields: role',
            ],
            'multiple missing' => [
                ['email' => 'test@example.com'],
                'Missing required fields: name, password, role',
            ],
        ];
    }

    public function testFromApiArrayFailsWithInvalidEmail(): void
    {
        $data = [
            'email' => 'invalid-email-format',
            'password' => 'ValidPass123!',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER',
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        RegisterUserCommand::fromApiArray($data);
    }

    public function testFromApiArrayFailsWithWeakPassword(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'weak',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER',
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        RegisterUserCommand::fromApiArray($data);
    }

    public function testFromApiArrayFailsWithInvalidRole(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'ValidPass123!',
            'name' => 'Valid Name',
            'role' => 'INVALID_ROLE',
        ];

        $this->expectException(UserValidationDomainDomainException::class);

        RegisterUserCommand::fromApiArray($data);
    }

    public function testFromApiArrayFailsWithEmptyFields(): void
    {
        $data = [
            'email' => '',
            'password' => 'ValidPass123!',
            'name' => 'Valid Name',
            'role' => 'ROLE_READER',
        ];

        $this->expectException(UserValidationDomainDomainException::class);
        $this->expectExceptionMessage('Missing required fields: email');

        RegisterUserCommand::fromApiArray($data);
    }
}
